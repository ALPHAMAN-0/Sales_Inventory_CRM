<?php

namespace Database\Factories;

use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Sales\Enums\SaleStatus;
use App\Domain\Sales\Models\Sale;
use App\Domain\Support\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'invoice_number' => strtoupper(fake()->unique()->bothify('INV-2026-######')),
            'customer_id' => Customer::factory(),
            'employee_id' => Employee::factory(),
            'branch_id' => Branch::factory(),
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'status' => SaleStatus::Completed,
            'sold_at' => now(),
        ];
    }
}
