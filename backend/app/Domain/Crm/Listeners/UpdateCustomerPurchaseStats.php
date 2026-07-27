<?php

namespace App\Domain\Crm\Listeners;

use App\Domain\Crm\Services\CustomerLifecycleService;
use App\Domain\Sales\Events\SaleCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Side effect of a sale: refresh the buyer's denormalized aggregates and
 * advance their lifecycle state. Queued + single-responsibility.
 */
class UpdateCustomerPurchaseStats implements ShouldQueue
{
    public function __construct(private readonly CustomerLifecycleService $lifecycle) {}

    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale;

        if ($sale->customer) {
            $this->lifecycle->syncPurchaseStats($sale->customer, $sale);
        }
    }
}
