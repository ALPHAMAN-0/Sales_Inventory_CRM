<?php

namespace App\Domain\Crm\Listeners;

use App\Domain\Crm\Services\KpiService;
use App\Domain\Sales\Events\SaleCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Side effect of a sale: if the buyer was a lost customer assigned to an
 * employee, credit that employee's KPI — exactly once. Queued + idempotent
 * (see KpiService::creditRecovery).
 */
class CreditRecoveryKpi implements ShouldQueue
{
    public function __construct(private readonly KpiService $kpi) {}

    public function handle(SaleCompleted $event): void
    {
        $this->kpi->creditRecovery($event->sale);
    }
}
