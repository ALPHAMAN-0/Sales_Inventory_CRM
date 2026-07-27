<?php

namespace App\Console\Commands;

use App\Domain\Crm\Models\Employee;
use App\Domain\Crm\Services\KpiService;
use Illuminate\Console\Command;

class RebuildKpi extends Command
{
    protected $signature = 'kpi:rebuild';

    protected $description = 'Rebuild cached employee kpi_score values from the kpi_events ledger.';

    public function handle(KpiService $kpi): int
    {
        $count = 0;

        Employee::query()->chunkById(500, function ($employees) use ($kpi, &$count) {
            foreach ($employees as $employee) {
                $kpi->rebuildScore($employee);
                $count++;
            }
        });

        $this->info("Rebuilt kpi_score for {$count} employee(s) from the ledger.");

        return self::SUCCESS;
    }
}
