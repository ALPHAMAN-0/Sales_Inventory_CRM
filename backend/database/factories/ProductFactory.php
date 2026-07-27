<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'price' => fake()->randomFloat(2, 5, 500),
            'is_active' => true,
        ];
    }

    /**
     * Create the product AND its inventory row at $quantity for $branch, plus a
     * matching opening `purchase` ledger row so the cache == ledger from birth.
     */
    public function withStock(Branch $branch, int $quantity): static
    {
        return $this->afterCreating(function (Product $product) use ($branch, $quantity) {
            Inventory::create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'quantity' => $quantity,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'type' => StockMovementType::Purchase,
                'quantity' => $quantity,
                'balance_after' => $quantity,
                'note' => 'Opening balance',
            ]);
        });
    }
}
