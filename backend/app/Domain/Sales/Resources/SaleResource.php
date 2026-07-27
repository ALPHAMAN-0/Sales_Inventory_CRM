<?php

namespace App\Domain\Sales\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'branch_id' => $this->branch_id,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'status' => $this->status?->value,
            'sold_at' => $this->sold_at,
            'created_at' => $this->created_at,
        ];
    }
}
