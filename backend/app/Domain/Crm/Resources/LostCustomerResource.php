<?php

namespace App\Domain\Crm\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A lost customer plus its most recent assignment (if any) — the CRM
 * follow-up list. Expects `assignments.employee` to be eager-loaded.
 */
class LostCustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assignment = $this->assignments?->sortByDesc('assigned_at')->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'last_purchase_at' => $this->last_purchase_at,
            'days_since_last_purchase' => $this->last_purchase_at
                ? (int) $this->last_purchase_at->diffInDays(now())
                : null,
            'total_orders' => (int) $this->total_orders,
            'total_spent' => (float) $this->total_spent,
            'assignment' => $assignment ? [
                'id' => $assignment->id,
                'employee_id' => $assignment->employee_id,
                'employee_name' => $assignment->employee?->name,
                'status' => $assignment->status?->value,
                'assigned_at' => $assignment->assigned_at,
            ] : null,
        ];
    }
}
