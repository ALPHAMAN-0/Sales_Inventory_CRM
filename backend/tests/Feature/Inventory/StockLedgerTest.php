<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Support\Models\Branch;

it('writes a correct signed ledger row and keeps cache == ledger', function () {
    $branch = Branch::factory()->create();
    $product = Product::factory()->withStock($branch, 10)->create();
    $customer = Customer::factory()->create();
    $employee = Employee::factory()->create();

    orderFor($customer, [[$product->id, 3]], $branch, $employee);

    expect(stockOf($product, $branch))->toBe(7)        // 10 - 3
        ->and(ledgerSum($product, $branch))->toBe(7);  // opening +10, sale -3

    $movement = StockMovement::query()
        ->where('type', 'sale')
        ->where('product_id', $product->id)
        ->first();

    expect((int) $movement->quantity)->toBe(-3)
        ->and((int) $movement->balance_after)->toBe(7)
        ->and($movement->reference_type)->toBe('sale');
});
