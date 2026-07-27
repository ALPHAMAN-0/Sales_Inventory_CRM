<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Crm\Models\KpiEvent;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Sales\Actions\CreateSaleAction;
use App\Domain\Sales\DTOs\CreateSaleData;
use App\Domain\Sales\DTOs\SaleLineData;
use App\Domain\Sales\Models\Sale;
use App\Domain\Support\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test-case bindings
|--------------------------------------------------------------------------
| Feature tests wrap each case in a transaction (RefreshDatabase). The
| Concurrency suite must NOT — its child processes need COMMITTED rows to
| contend for real InnoDB row locks, so it manages its own schema.
*/
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Concurrency');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Record a sale through the REAL action (exercises lock + snapshot + ledger).
 *
 * @param  array<int, array{0:int, 1:int}>  $lines  [[productId, qty], ...]
 */
function orderFor(Customer $customer, array $lines, Branch $branch, Employee $employee): Sale
{
    return app(CreateSaleAction::class)->execute(new CreateSaleData(
        customerId: $customer->id,
        employeeId: $employee->id,
        branchId: $branch->id,
        items: array_map(fn ($line) => new SaleLineData($line[0], $line[1]), $lines),
    ));
}

function stockOf(Product $product, Branch $branch): int
{
    return (int) Inventory::query()
        ->where('product_id', $product->id)
        ->where('branch_id', $branch->id)
        ->value('quantity');
}

function ledgerSum(Product $product, Branch $branch): int
{
    return (int) StockMovement::query()
        ->where('product_id', $product->id)
        ->where('branch_id', $branch->id)
        ->sum('quantity');
}

function kpiSum(Employee $employee): int
{
    return (int) KpiEvent::query()->where('employee_id', $employee->id)->sum('points');
}

/** Ensure the spatie roles exist for auth/policy tests. */
function seedRoles(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::findOrCreate('admin');
    Role::findOrCreate('employee');
}
