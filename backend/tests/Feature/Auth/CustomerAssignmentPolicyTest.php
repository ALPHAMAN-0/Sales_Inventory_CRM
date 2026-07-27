<?php

use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => seedRoles());

it('forbids a regular employee from assigning customers', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');
    Sanctum::actingAs($user);

    $employee = Employee::factory()->create();
    $customer = Customer::factory()->lost()->create();

    $this->postJson("/api/v1/crm/customers/{$customer->id}/assign", ['employee_id' => $employee->id])
        ->assertForbidden();
});

it('allows an admin to assign a lost customer', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    Sanctum::actingAs($user);

    $employee = Employee::factory()->create();
    $customer = Customer::factory()->lost()->create();

    $this->postJson("/api/v1/crm/customers/{$customer->id}/assign", ['employee_id' => $employee->id])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});
