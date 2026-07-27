<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Sales\Events\SaleCompleted;
use App\Domain\Support\Models\Branch;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Facades\Event;

it('dispatches SaleCompleted on a successful sale', function () {
    Event::fake([SaleCompleted::class]);

    $branch = Branch::factory()->create();
    $product = Product::factory()->withStock($branch, 5)->create();
    $customer = Customer::factory()->create();
    $employee = Employee::factory()->create();

    orderFor($customer, [[$product->id, 1]], $branch, $employee);

    Event::assertDispatched(SaleCompleted::class);
});

it('dispatches nothing when the sale fails on insufficient stock', function () {
    Event::fake([SaleCompleted::class]);

    $branch = Branch::factory()->create();
    $product = Product::factory()->withStock($branch, 0)->create();
    $customer = Customer::factory()->create();
    $employee = Employee::factory()->create();

    expect(fn () => orderFor($customer, [[$product->id, 1]], $branch, $employee))
        ->toThrow(InsufficientStockException::class);

    Event::assertNotDispatched(SaleCompleted::class);
});

it('marks SaleCompleted to fire only after the transaction commits', function () {
    expect(is_subclass_of(SaleCompleted::class, ShouldDispatchAfterCommit::class))->toBeTrue();
});
