<?php

namespace App\Domain\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'is_active' => (bool) $this->is_active,
            'stock' => $this->whenLoaded('inventories', fn () => $this->inventories->map(fn ($inventory) => [
                'branch_id' => $inventory->branch_id,
                'branch_name' => $inventory->branch?->name,
                'quantity' => (int) $inventory->quantity,
            ])->values()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
