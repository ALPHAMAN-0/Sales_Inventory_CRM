<?php

namespace Database\Factories;

use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\CustomerAssignment;
use App\Domain\Crm\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAssignment>
 */
class CustomerAssignmentFactory extends Factory
{
    protected $model = CustomerAssignment::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory()->lost(),
            'employee_id' => Employee::factory(),
            'assigned_by' => User::factory(),
            'status' => AssignmentStatus::Pending,
            'assigned_at' => now(),
        ];
    }
}
