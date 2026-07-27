<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Support\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => Cache::flush());

it('requires authentication for the store feed', function () {
    $this->getJson('/api/v1/store/products')->assertUnauthorized();
});

it('requires the store:read token ability', function () {
    Sanctum::actingAs(User::factory()->create(), []);   // token without abilities

    $this->getJson('/api/v1/store/products')->assertForbidden();
});

it('exposes only whitelisted fields to a store:read client', function () {
    $branch = Branch::factory()->create();
    Product::factory()->withStock($branch, 7)->create([
        'name' => 'Widget',
        'sku' => 'SKU-TEST-01',
        'price' => 12.50,
    ]);

    Sanctum::actingAs(User::factory()->create(), ['store:read']);

    $response = $this->getJson('/api/v1/store/products')->assertOk();

    $first = $response->json('data.0');

    expect(array_keys($first))->toBe(['sku', 'name', 'price', 'available_stock'])
        ->and($first['sku'])->toBe('SKU-TEST-01')
        ->and($first['available_stock'])->toBe(7);
});
