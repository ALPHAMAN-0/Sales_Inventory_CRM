<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'branch_id' => Branch::factory(),
            'type' => StockMovementType::Purchase,
            'quantity' => 10,
            'balance_after' => 10,
        ];
    }
}
