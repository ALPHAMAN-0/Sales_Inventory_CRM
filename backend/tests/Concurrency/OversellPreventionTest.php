<?php

/*
|--------------------------------------------------------------------------
| Flagship correctness test: oversell is impossible under real concurrency.
|--------------------------------------------------------------------------
| Deliberately NOT wrapped in RefreshDatabase — two separate OS processes
| open their own MySQL connections and contend for the same InnoDB row lock
| via SELECT ... FOR UPDATE, exactly as concurrent HTTP requests would. The
| loser blocks until the winner commits, re-reads quantity 0, and throws
| InsufficientStockException. Runs against MySQL only (SQLite has no real
| FOR UPDATE, so it would pass trivially and prove nothing).
*/

use App\Domain\Catalog\Models\Product;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Support\Models\Branch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    // Clean, COMMITTED schema (child processes must see the fixtures).
    Artisan::call('migrate:fresh', ['--force' => true]);
});

it('never oversells the last unit when two buyers race', function () {
    $branch = Branch::factory()->create();
    $product = Product::factory()->withStock($branch, 1)->create();   // exactly ONE unit
    $employee = Employee::factory()->create();
    $buyerA = Customer::factory()->create();
    $buyerB = Customer::factory()->create();

    // Child processes are fresh `php artisan` boots — hand them the full testing
    // env (defined in tests/bootstrap.php) so they hit sales_crm_testing, not dev.
    $spawn = fn (Customer $customer) => Process::path(base_path())
        ->env(TESTING_ENV)
        ->timeout(30)
        ->start(sprintf(
            'php artisan test:record-sale %d %d %d 1 %d',
            $product->id, $branch->id, $customer->id, $employee->id,
        ));

    $p1 = $spawn($buyerA);
    $p2 = $spawn($buyerB);
    $out = trim($p1->wait()->output())."\n".trim($p2->wait()->output());

    expect(substr_count($out, 'OK:'))->toBe(1)                       // exactly one succeeds
        ->and(substr_count($out, 'FAIL:InsufficientStock'))->toBe(1) // exactly one is refused
        ->and(stockOf($product, $branch))->toBe(0)                   // never negative
        ->and(ledgerSum($product, $branch))->toBe(0);                // opening +1, one sale -1

    expect(StockMovement::query()->where('type', 'sale')->count())->toBe(1);
});

it('has a database CHECK constraint as the hard backstop', function () {
    $branch = Branch::factory()->create();
    $product = Product::factory()->withStock($branch, 0)->create();

    expect(fn () => DB::table('inventories')
        ->where('product_id', $product->id)
        ->where('branch_id', $branch->id)
        ->update(['quantity' => -1]))
        ->toThrow(Illuminate\Database\QueryException::class);
});
