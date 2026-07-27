<?php

namespace Database\Factories;

use App\Domain\Crm\Models\Employee;
use App\Domain\Support\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'branch_id' => Branch::factory(),
            'kpi_score' => 0,
        ];
    }
}
