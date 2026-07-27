<?php

namespace App\Domain\Crm\Actions;

use App\Domain\Crm\DTOs\AssignCustomerData;
use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\CustomerAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Assign a lost customer to an employee for recovery follow-up. Creates a
 * `pending` assignment which CreditRecoveryKpi later resolves on purchase.
 */
final class AssignCustomerAction
{
    public function execute(AssignCustomerData $data): CustomerAssignment
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::query()->whereKey($data->customerId)->lockForUpdate()->firstOrFail();

            if ($customer->status !== CustomerStatus::Lost) {
                throw ValidationException::withMessages([
                    'customer_id' => ['Only lost customers can be assigned.'],
                ]);
            }

            $hasOpenAssignment = CustomerAssignment::query()
                ->where('customer_id', $customer->id)
                ->whereIn('status', array_map(fn ($s) => $s->value, AssignmentStatus::open()))
                ->exists();

            if ($hasOpenAssignment) {
                throw ValidationException::withMessages([
                    'customer_id' => ['This customer already has an open assignment.'],
                ]);
            }

            return CustomerAssignment::create([
                'customer_id' => $customer->id,
                'employee_id' => $data->employeeId,
                'assigned_by' => $data->assignedBy,
                'status' => AssignmentStatus::Pending,
                'assigned_at' => now(),
            ]);
        });
    }
}
