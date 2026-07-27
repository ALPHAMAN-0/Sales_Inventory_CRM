<?php

namespace Database\Seeders;

use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Engineers customers into every lifecycle state so the whole flow is
 * demoable on a fresh seed.
 */
class CustomerLifecycleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@salescrm.test')->first();
        $employee = Employee::where('email', 'alice@salescrm.test')->first() ?? Employee::first();

        // Recently active.
        Customer::factory()->count(10)->active()->create();

        // Active but stale — customers:detect-lost will flag exactly these.
        Customer::factory()->count(5)->create([
            'status' => CustomerStatus::Active,
            'last_purchase_at' => now()->subDays(120),
        ]);

        // Already lost AND assigned to Alice (pending) — record a sale for one
        // of these and the KPI recovery credit fires immediately.
        Customer::factory()->count(5)->assigned($employee, $admin)->create();

        // A few plain lost customers (no assignment yet) for the assign flow.
        Customer::factory()->count(5)->lost()->create();
    }
}
