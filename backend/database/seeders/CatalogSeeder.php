<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        Product::factory()->count(30)->create()->each(function (Product $product) use ($branches) {
            foreach ($branches as $branch) {
                $quantity = random_int(0, 80);

                Inventory::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'quantity' => $quantity,
                ]);

                // Opening-balance ledger row so cache == ledger from day one.
                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'type' => StockMovementType::Purchase,
                    'quantity' => $quantity,
                    'balance_after' => $quantity,
                    'note' => 'Opening balance',
                ]);
            }
        });
    }
}
