<?php

namespace App\Domain\Crm\Services;

use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Crm\Enums\KpiReason;
use App\Domain\Crm\Models\CustomerAssignment;
use App\Domain\Crm\Models\Employee;
use App\Domain\Crm\Models\KpiEvent;
use App\Domain\Sales\Models\Sale;
use Illuminate\Support\Facades\DB;

class KpiService
{
    /**
     * Credit the assigned employee for recovering a lost customer — EXACTLY
     * ONCE. Idempotency is structural: we lock the customer's open assignment
     * and immediately close it (pending/contacted -> recovered), so a second
     * purchase finds nothing to credit. The unique(assignment_id) on kpi_events
     * is the DB-level backstop. Returns null when there's no recovery to credit.
     */
    public function creditRecovery(Sale $sale): ?KpiEvent
    {
        return DB::transaction(function () use ($sale) {
            $assignment = CustomerAssignment::query()
                ->where('customer_id', $sale->customer_id)
                ->whereIn('status', array_map(fn ($s) => $s->value, AssignmentStatus::open()))
                ->orderBy('assigned_at')
                ->lockForUpdate()
                ->first();

            if (! $assignment) {
                return null;   // customer wasn't in a recovery flow — no KPI
            }

            $points = (int) config('crm.kpi.recovery_points', 10);

            $assignment->update([
                'status' => AssignmentStatus::Recovered,   // closes the window (idempotent)
                'recovered_at' => now(),
                'sale_id' => $sale->id,
            ]);

            $event = KpiEvent::create([
                'employee_id' => $assignment->employee_id,
                'customer_id' => $sale->customer_id,
                'assignment_id' => $assignment->id,
                'sale_id' => $sale->id,
                'points' => $points,
                'reason' => KpiReason::CustomerRecovery,
            ]);

            Employee::query()->whereKey($assignment->employee_id)->increment('kpi_score', $points);

            return $event;
        });
    }

    /**
     * Rebuild the cached kpi_score from the ledger (the counter is only a cache).
     */
    public function rebuildScore(Employee $employee): int
    {
        $sum = (int) KpiEvent::query()->where('employee_id', $employee->id)->sum('points');
        $employee->update(['kpi_score' => $sum]);

        return $sum;
    }
}
