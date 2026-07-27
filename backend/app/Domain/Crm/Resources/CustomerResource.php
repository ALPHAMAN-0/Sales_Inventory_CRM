<?php

namespace App\Domain\Crm\Resources;

use App\Domain\Sales\Resources\SaleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'total_orders' => (int) $this->total_orders,
            'total_spent' => (float) $this->total_spent,
            'first_purchase_at' => $this->first_purchase_at,
            'last_purchase_at' => $this->last_purchase_at,
            'purchase_frequency_days' => $this->purchaseFrequencyDays(),
            'orders' => SaleResource::collection($this->whenLoaded('sales')),
        ];
    }

    private function purchaseFrequencyDays(): ?float
    {
        if ($this->total_orders > 1 && $this->first_purchase_at && $this->last_purchase_at) {
            $span = $this->first_purchase_at->diffInDays($this->last_purchase_at);

            return $span > 0 ? round($span / ($this->total_orders - 1), 1) : 0.0;
        }

        return null;
    }
}
