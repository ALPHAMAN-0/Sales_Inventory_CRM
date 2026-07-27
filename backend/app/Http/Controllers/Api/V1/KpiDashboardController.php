<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Crm\Models\Employee;
use App\Domain\Crm\Models\KpiEvent;
use App\Domain\Crm\Resources\EmployeeKpiResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KpiDashboardController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $employees = Employee::query()
            ->with('branch')
            ->withCount(['kpiEvents', 'sales'])
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->orderByDesc('kpi_score')
            ->orderBy('name')
            ->get();

        $rank = 0;
        $employees->each(function (Employee $employee) use (&$rank) {
            $employee->rank = ++$rank;
        });

        return EmployeeKpiResource::collection($employees);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->loadCount(['kpiEvents', 'sales']);

        $events = KpiEvent::query()
            ->where('employee_id', $employee->id)
            ->with(['customer', 'sale'])
            ->latest('id')
            ->get()
            ->map(fn (KpiEvent $event) => [
                'id' => $event->id,
                'points' => $event->points,
                'reason' => $event->reason?->value,
                'customer_name' => $event->customer?->name,
                'sale_id' => $event->sale_id,
                'occurred_at' => $event->created_at,
            ]);

        return response()->json([
            'data' => [
                'employee' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'branch_id' => $employee->branch_id,
                    'kpi_score' => (int) $employee->kpi_score,
                    'recovered_customers' => (int) $employee->kpi_events_count,
                    'sales_count' => (int) $employee->sales_count,
                ],
                'events' => $events,
            ],
        ]);
    }
}
