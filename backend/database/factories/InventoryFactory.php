<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'branch_id' => Branch::factory(),
            'quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
