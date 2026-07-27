<?php

namespace App\Providers;

use App\Domain\Crm\Listeners\CreditRecoveryKpi;
use App\Domain\Crm\Listeners\UpdateCustomerPurchaseStats;
use App\Domain\Sales\Events\SaleCompleted;
use App\Domain\Sales\Listeners\SendInvoice;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Re-added explicitly (Laravel's skeleton omits it). Event auto-discovery
 * only scans app/Listeners, but our listeners live in app/Domain/*, so we
 * map them here. SaleCompleted fans out to three single-responsibility,
 * queued, after-commit listeners.
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SaleCompleted::class => [
            UpdateCustomerPurchaseStats::class,  // customer aggregates + lifecycle
            CreditRecoveryKpi::class,            // idempotent recovery KPI credit
            SendInvoice::class,                  // queued dompdf invoice → mail
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
