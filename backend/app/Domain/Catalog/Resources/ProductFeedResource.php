<?php

namespace App\Domain\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The third-party e-commerce contract. Exposes ONLY these four fields — never
 * the internal model (no id, cost, timestamps, branch breakdown, etc.).
 * `available_stock` is expected to be set on the resource by the controller
 * (summed across branches, from the Redis-cached feed).
 */
class ProductFeedResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sku' => $this->resource['sku'],
            'name' => $this->resource['name'],
            'price' => (float) $this->resource['price'],
            'available_stock' => (int) $this->resource['available_stock'],
        ];
    }
}
