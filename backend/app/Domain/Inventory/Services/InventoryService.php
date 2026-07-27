<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Cohesive inventory operations for NON-sale movements (opening balances,
 * purchases, manual adjustments, returns) plus ledger reconciliation.
 *
 * The sale path deliberately does NOT go through here — CreateSaleAction locks
 * and decrements inline so the lock scope is explicit and multi-line atomic.
 */
class InventoryService
{
    /**
     * Apply a signed quantity delta, writing one ledger row and updating the
     * cached quantity, all under a row lock.
     */
    public function applyMovement(
        Product $product,
        Branch $branch,
        int $delta,
        StockMovementType $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $branch, $delta, $type, $reference, $userId, $note) {
            $inventory = $this->lockOrCreate($product->id, $branch->id);

            $balanceAfter = $inventory->quantity + $delta;
            $inventory->quantity = $balanceAfter;   // CHECK(quantity>=0) is the DB backstop
            $inventory->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'type' => $type,
                'quantity' => $delta,
                'balance_after' => $balanceAfter,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $userId,
                'note' => $note,
            ]);
        });
    }

    /**
     * Rebuild the cached quantity from the ledger (the counter is only a cache).
     */
    public function rebuildQuantity(int $productId, int $branchId): int
    {
        $sum = (int) StockMovement::query()
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->sum('quantity');

        Inventory::query()->updateOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => $sum],
        );

        return $sum;
    }

    private function lockOrCreate(int $productId, int $branchId): Inventory
    {
        $inventory = Inventory::query()
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->first();

        if ($inventory) {
            return $inventory;
        }

        Inventory::query()->firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => 0],
        );

        return Inventory::query()
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->first();
    }
}
