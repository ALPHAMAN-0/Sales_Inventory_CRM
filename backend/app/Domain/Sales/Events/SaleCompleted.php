<?php

namespace App\Domain\Sales\Events;

use App\Domain\Sales\Models\Sale;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once a sale is durably committed. Implements ShouldDispatchAfterCommit
 * so listeners can never observe (or email an invoice for) a rolled-back sale.
 * SerializesModels re-fetches the Sale by id when the queued listener runs.
 */
class SaleCompleted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Sale $sale) {}
}
