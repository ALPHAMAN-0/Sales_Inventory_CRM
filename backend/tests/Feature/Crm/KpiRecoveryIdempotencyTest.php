<?php

use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Crm\Models\KpiEvent;
use App\Domain\Crm\Services\KpiService;
use App\Domain\Sales\Models\Sale;
use App\Domain\Support\Models\Branch;

it('credits the assigned employee exactly once and is idempotent', function () {
    $branch = Branch::factory()->create();
    $employee = Employee::factory()->create();
    $customer = Customer::factory()->assigned($employee)->create();   // lost + pending assignment

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'employee_id' => $employee->id,
        'branch_id' => $branch->id,
    ]);

    $kpi = app(KpiService::class);

    $first = $kpi->creditRecovery($sale);    // credits
    $second = $kpi->creditRecovery($sale);   // second purchase finds no open assignment

    expect($first)->not->toBeNull()
        ->and($second)->toBeNull()
        ->and(KpiEvent::query()->where('employee_id', $employee->id)->count())->toBe(1)
        ->and($employee->fresh()->kpi_score)->toBe(10)
        ->and(kpiSum($employee))->toBe(10);   // cache == ledger

    $assignment = $customer->assignments()->first();
    expect($assignment->status)->toBe(AssignmentStatus::Recovered)
        ->and($assignment->sale_id)->toBe($sale->id);
});

it('awards no KPI when the buyer was never in a recovery flow', function () {
    $branch = Branch::factory()->create();
    $employee = Employee::factory()->create();
    $customer = Customer::factory()->create();   // active, no assignment

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'employee_id' => $employee->id,
        'branch_id' => $branch->id,
    ]);

    expect(app(KpiService::class)->creditRecovery($sale))->toBeNull()
        ->and($employee->fresh()->kpi_score)->toBe(0);
});
