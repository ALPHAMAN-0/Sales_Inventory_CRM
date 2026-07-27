<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Resources\ProductFeedResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * Read-only third-party e-commerce feed. Redis-cached so external traffic
 * never touches hot inventory rows. Exposes only whitelisted fields via
 * ProductFeedResource.
 */
class ProductFeedController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $feed = Cache::remember('store:feed', (int) config('crm.store.cache_ttl', 300), function () {
            return Product::query()
                ->where('is_active', true)
                ->with('inventories')
                ->orderBy('name')
                ->get()
                ->map(fn (Product $product) => [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'available_stock' => (int) $product->inventories->sum('quantity'),
                ])
                ->values()
                ->all();
        });

        return ProductFeedResource::collection($feed);
    }

    public function show(string $sku): ProductFeedResource
    {
        $item = Cache::remember("store:feed:{$sku}", (int) config('crm.store.cache_ttl', 300), function () use ($sku) {
            $product = Product::query()
                ->where('sku', $sku)
                ->where('is_active', true)
                ->with('inventories')
                ->first();

            return $product ? [
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float) $product->price,
                'available_stock' => (int) $product->inventories->sum('quantity'),
            ] : null;
        });

        abort_if($item === null, 404);

        return new ProductFeedResource($item);
    }
}
