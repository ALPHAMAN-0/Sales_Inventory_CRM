<?php

namespace Database\Seeders;

use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Sales\Enums\SaleStatus;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Models\SaleItem;
use App\Domain\Sales\Services\InvoiceNumberGenerator;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Backfills realistic historical SALES + line items + inventory ledger movements
 * for every seeded customer, so the app is fully testable immediately after
 * seeding: the sales list, per-customer purchase history, employee/branch
 * reporting, and the stock ledger all carry real data.
 *
 * Sales are dated to MATCH each customer's already-seeded lifecycle — the latest
 * sale lands on their last_purchase_at, earlier ones spread back to
 * first_purchase_at — and the denormalized aggregates (total_orders / total_spent
 * / first_ / last_purchase_at) are then recomputed FROM these real sales. So
 * lost/stale/active customers keep their state (detect-lost and the KPI-recovery
 * demo still work), but backed by genuine transactions instead of fabricated
 * counters. It never dispatches SaleCompleted, so seeding produces no invoice
 * emails or KPI credits — those stay reserved for live actions.
 */
class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $branchesById = Branch::all()->keyBy('id');
        $employees = Employee::all();

        if ($branchesById->isEmpty() || $employees->isEmpty()) {
            return;
        }

        $invoices = app(InvoiceNumberGenerator::class);

        Customer::query()
            ->whereNotNull('last_purchase_at')
            ->get()
            ->each(function (Customer $customer) use ($branchesById, $employees, $invoices) {
                $last = $customer->last_purchase_at->copy();
                $first = $customer->first_purchase_at?->copy() ?? $last->copy()->subDays(30);
                if ($first->greaterThan($last)) {
                    $first = $last->copy()->subDays(30);
                }

                $count = max(1, min((int) ($customer->total_orders ?: 1), 4));

                foreach ($this->saleDates($first, $last, $count) as $soldAt) {
                    $employee = $employees->random();
                    $branch = $branchesById[$employee->branch_id] ?? $branchesById->random();
                    $this->recordSale($customer, $branch, $employee, $soldAt, $invoices);
                }

                $this->recomputeAggregates($customer);
            });
    }

    /**
     * The latest date is exactly $last (so last_purchase_at is preserved and the
     * lifecycle state stays valid); the rest fall between $first and $last.
     *
     * @return list<Carbon>
     */
    private function saleDates(Carbon $first, Carbon $last, int $count): array
    {
        $dates = [$last->copy()];
        $span = max(1, (int) $first->diffInDays($last));

        for ($i = 1; $i < $count; $i++) {
            $dates[] = $last->copy()->subDays(random_int(1, $span));
        }

        return $dates;
    }

    private function recordSale(
        Customer $customer,
        Branch $branch,
        Employee $employee,
        Carbon $soldAt,
        InvoiceNumberGenerator $invoices,
    ): void {
        DB::transaction(function () use ($customer, $branch, $employee, $soldAt, $invoices) {
            // Only products that actually have stock at this branch right now.
            $lines = Inventory::query()
                ->where('branch_id', $branch->id)
                ->where('quantity', '>', 0)
                ->with('product')
                ->inRandomOrder()
                ->take(random_int(1, 2))
                ->lockForUpdate()
                ->get()
                ->filter(fn (Inventory $inv) => $inv->product?->is_active);

            if ($lines->isEmpty()) {
                return; // branch sold out — skip
            }

            $sale = Sale::create([
                'invoice_number' => $invoices->next($branch->id),
                'customer_id' => $customer->id,
                'employee_id' => $employee->id,
                'branch_id' => $branch->id,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'status' => SaleStatus::Completed,
                'sold_at' => $soldAt,
                'created_at' => $soldAt,
                'updated_at' => $soldAt,
            ]);

            foreach ($lines as $inventory) {
                $qty = min($inventory->quantity, random_int(1, 3));
                $unitPrice = (float) $inventory->product->price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $inventory->product_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => round($unitPrice * $qty, 2),
                ]);

                $inventory->quantity -= $qty;
                $inventory->save();

                StockMovement::create([
                    'product_id' => $inventory->product_id,
                    'branch_id' => $branch->id,
                    'type' => StockMovementType::Sale,
                    'quantity' => -$qty,
                    'balance_after' => $inventory->quantity,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'user_id' => $employee->user_id,
                    'created_at' => $soldAt,
                ]);
            }

            $sale->recalculateTotals()->save();
        });
    }

    /** Rebuild the denormalized counters from the real sales just created. */
    private function recomputeAggregates(Customer $customer): void
    {
        $stats = Sale::query()
            ->where('customer_id', $customer->id)
            ->where('status', SaleStatus::Completed->value)
            ->selectRaw('COUNT(*) AS c, COALESCE(SUM(total), 0) AS s, MIN(sold_at) AS f, MAX(sold_at) AS l')
            ->first();

        if ((int) $stats->c === 0) {
            return; // no sales landed (branch sold out) — leave the seeded values
        }

        // forceFill so we can write timestamp columns; status is intentionally
        // left untouched so lost/stale/active states are preserved.
        $customer->forceFill([
            'total_orders' => (int) $stats->c,
            'total_spent' => (float) $stats->s,
            'first_purchase_at' => $stats->f,
            'last_purchase_at' => $stats->l,
        ])->save();
    }
}
