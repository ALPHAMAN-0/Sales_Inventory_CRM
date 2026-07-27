<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('inventories.branch')
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate((int) $request->integer('per_page', 20));

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('inventories.branch'));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return (new ProductResource($product->load('inventories.branch')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->load('inventories.branch'));
    }

    public function destroy(Product $product): Response
    {
        $this->authorize('delete', $product);
        $product->delete();

        return response()->noContent();
    }
}
