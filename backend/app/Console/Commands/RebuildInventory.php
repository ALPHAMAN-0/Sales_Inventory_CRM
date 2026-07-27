<?php

namespace App\Console\Commands;

use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Console\Command;

class RebuildInventory extends Command
{
    protected $signature = 'inventory:rebuild';

    protected $description = 'Rebuild cached inventory quantities from the stock_movements ledger.';

    public function handle(InventoryService $inventory): int
    {
        $pairs = StockMovement::query()
            ->select('product_id', 'branch_id')
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            $qty = $inventory->rebuildQuantity($pair->product_id, $pair->branch_id);
            $this->line("product {$pair->product_id} @ branch {$pair->branch_id} => {$qty}");
        }

        $this->info("Rebuilt {$pairs->count()} inventory row(s) from the ledger.");

        return self::SUCCESS;
    }
}
