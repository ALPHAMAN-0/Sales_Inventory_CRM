<?php

namespace App\Domain\Crm\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'status' => $this->status?->value,
            'assigned_at' => $this->assigned_at,
            'recovered_at' => $this->recovered_at,
            'sale_id' => $this->sale_id,
        ];
    }
}
