<?php

namespace Database\Factories;

use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\CustomerAssignment;
use App\Domain\Crm\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.7)->numerify('+1##########'),
            'status' => CustomerStatus::Active,
            'first_purchase_at' => now()->subDays(60),
            'last_purchase_at' => now()->subDays(fake()->numberBetween(1, 20)),
            'total_orders' => fake()->numberBetween(1, 10),
            'total_spent' => fake()->randomFloat(2, 50, 3000),
        ];
    }

    /** Recently active. */
    public function active(): static
    {
        return $this->state([
            'status' => CustomerStatus::Active,
            'last_purchase_at' => now()->subDays(5),
        ]);
    }

    /** Already flagged lost (last purchase past the threshold). */
    public function lost(): static
    {
        return $this->state([
            'status' => CustomerStatus::Lost,
            'last_purchase_at' => now()->subDays((int) config('crm.lost_threshold_days', 90) + 30),
        ]);
    }

    /** Lost AND assigned to an employee (pending) — the KPI-recovery demo path. */
    public function assigned(Employee $employee, ?User $by = null): static
    {
        return $this->lost()->afterCreating(function (Customer $customer) use ($employee, $by) {
            CustomerAssignment::create([
                'customer_id' => $customer->id,
                'employee_id' => $employee->id,
                'assigned_by' => $by?->id ?? $employee->user_id,
                'status' => AssignmentStatus::Pending,
                'assigned_at' => now(),
            ]);
        });
    }
}
