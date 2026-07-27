<?php

namespace App\Domain\Sales\Listeners;

use App\Domain\Sales\Events\SaleCompleted;
use App\Domain\Sales\Mail\InvoiceMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Side effect of a sale: email the customer a PDF invoice. Queued, so a slow
 * or down mail server never blocks (or fails) the sale itself.
 */
class SendInvoice implements ShouldQueue
{
    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale->load(['items.product', 'customer', 'employee', 'branch']);

        if (! $sale->customer?->email) {
            return;   // nothing to send to
        }

        Mail::to($sale->customer->email)->send(new InvoiceMail($sale));
    }
}
