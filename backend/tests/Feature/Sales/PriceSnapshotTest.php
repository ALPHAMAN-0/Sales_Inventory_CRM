<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Support\Models\Branch;

it('freezes unit_price at sale time and never rewrites history on price change', function () {
    $branch = Branch::factory()->create();
    $product = Product::factory()->withStock($branch, 5)->create(['price' => 100.00]);
    $customer = Customer::factory()->create();
    $employee = Employee::factory()->create();

    $sale = orderFor($customer, [[$product->id, 2]], $branch, $employee);
    $item = $sale->items()->first();

    expect((float) $item->unit_price)->toBe(100.00)
        ->and((float) $sale->total)->toBe(210.00);   // 200 subtotal + 5% tax

    // Price changes AFTER the sale must not alter the historical invoice.
    $product->update(['price' => 250.00]);

    $sale->refresh();
    $item->refresh();

    expect((float) $item->unit_price)->toBe(100.00)
        ->and((float) $sale->subtotal)->toBe(200.00)
        ->and((float) $sale->total)->toBe(210.00);
});
