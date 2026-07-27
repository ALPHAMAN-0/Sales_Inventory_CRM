<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Sales\DTOs\CreateSaleData;
use App\Domain\Sales\Enums\SaleStatus;
use App\Domain\Sales\Events\SaleCompleted;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Services\InvoiceNumberGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Records a sale and deducts stock atomically. This is the correctness core:
 * two concurrent orders for the last unit resolve deterministically — one wins,
 * the other gets InsufficientStockException — because the involved inventory
 * rows are locked (SELECT ... FOR UPDATE) for the whole transaction.
 *
 * Lock-free alternative (documented, not used): a conditional atomic decrement
 *   UPDATE inventories SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?
 * treating zero affected rows as insufficient stock. Higher throughput under
 * extreme contention, but loses clean multi-line all-or-nothing pre-validation.
 * Pessimistic locking is chosen here for readability across multi-line orders.
 */
final class CreateSaleAction
{
    public function __construct(private readonly InvoiceNumberGenerator $invoiceNumbers) {}

    public function execute(CreateSaleData $data): Sale
    {
        $sale = DB::transaction(function () use ($data) {
            // 1. Lock every involved inventory row, ordered by product_id so
            //    concurrent multi-line sales acquire locks in the same order
            //    (deadlock-safe).
            $inventories = Inventory::query()
                ->where('branch_id', $data->branchId)
                ->whereIn('product_id', $data->productIds())
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $products = Product::query()
                ->whereIn('id', $data->productIds())
                ->get()
                ->keyBy('id');

            // 2. Validate sufficiency for EVERY line (aggregated per product)
            //    before mutating anything. Collect all shortages for a helpful 409.
            $requested = [];
            foreach ($data->items as $item) {
                $requested[$item->productId] = ($requested[$item->productId] ?? 0) + $item->quantity;
            }

            $shortages = [];
            foreach ($requested as $productId => $quantity) {
                $available = $inventories->get($productId)?->quantity ?? 0;
                if ($available < $quantity) {
                    $product = $products->get($productId);
                    $shortages[] = [
                        'product_id' => (int) $productId,
                        'product_name' => $product?->name ?? "#{$productId}",
                        'requested' => (int) $quantity,
                        'available' => (int) $available,
                    ];
                }
            }

            if ($shortages !== []) {
                throw new InsufficientStockException($shortages);
            }

            // 3. Sale header with a safely generated invoice number.
            $sale = Sale::create([
                'invoice_number' => $this->invoiceNumbers->next($data->branchId),
                'customer_id' => $data->customerId,
                'employee_id' => $data->employeeId,
                'branch_id' => $data->branchId,
                'status' => SaleStatus::Completed,
                'sold_at' => now(),
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
            ]);

            // 4. Per line: snapshot price, write item, decrement stock (same
            //    locked model instance accumulates across duplicate lines),
            //    append a signed ledger row.
            foreach ($data->items as $item) {
                $product = $products->get($item->productId);
                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $item->quantity, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,      // SNAPSHOT — immune to later price changes
                    'line_total' => $lineTotal,
                ]);

                $inventory = $inventories->get($item->productId);
                $inventory->quantity -= $item->quantity;   // CHECK(quantity>=0) is the DB backstop
                $inventory->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $data->branchId,
                    'type' => StockMovementType::Sale,
                    'quantity' => -$item->quantity,
                    'balance_after' => $inventory->quantity,
                    'reference_type' => $sale->getMorphClass(),
                    'reference_id' => $sale->id,
                ]);
            }

            $sale->recalculateTotals()->save();

            return $sale;
        });

        // 5. Side effects fire only after commit — never inside the transaction.
        SaleCompleted::dispatch($sale);

        return $sale->refresh()->load(['items', 'customer', 'employee']);
    }
}
