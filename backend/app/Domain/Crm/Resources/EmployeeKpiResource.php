<?php

namespace App\Domain\Crm\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A row in the KPI leaderboard. `rank` is injected by the controller;
 * counts come from withCount(['kpiEvents', 'sales']).
 */
class EmployeeKpiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employee_id' => $this->id,
            'employee_name' => $this->name,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'kpi_score' => (int) $this->kpi_score,
            'rank' => $this->rank ?? null,
            'recovered_customers' => (int) ($this->kpi_events_count ?? 0),
            'sales_count' => (int) ($this->sales_count ?? 0),
        ];
    }
}
