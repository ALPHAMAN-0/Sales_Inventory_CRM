<?php

namespace App\Providers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Crm\Models\Customer;
use App\Domain\Sales\Models\Sale;
use App\Policies\CustomerPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Polymorphic morph alias so stock_movements.reference_type stores
        // 'sale' rather than a brittle FQCN.
        Relation::morphMap([
            'sale' => Sale::class,
        ]);

        // Domain-namespaced models don't match Laravel's convention-based
        // policy resolver, so register them explicitly.
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);

        // Default SPA API limiter.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));

        // Dedicated, tighter limiter for the third-party e-commerce feed.
        RateLimiter::for('store', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));
    }
}
