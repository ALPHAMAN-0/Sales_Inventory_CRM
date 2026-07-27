<?php

namespace App\Console\Commands;

use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Sales\Actions\CreateSaleAction;
use App\Domain\Sales\DTOs\CreateSaleData;
use Illuminate\Console\Command;

/**
 * Test-only helper: record a single-unit sale through the REAL CreateSaleAction
 * so the concurrency test can spawn two competing OS processes that contend for
 * the same InnoDB row lock. Prints OK:<invoice> or FAIL:InsufficientStock.
 */
class TestRecordSale extends Command
{
    protected $signature = 'test:record-sale {product} {branch} {customer} {quantity=1} {employee?}';

    protected $description = '[testing] Record a sale via CreateSaleAction and report the outcome.';

    public function handle(CreateSaleAction $createSale): int
    {
        $employeeId = $this->argument('employee')
            ? (int) $this->argument('employee')
            : (int) Employee::query()->value('id');

        $data = new CreateSaleData(
            customerId: (int) $this->argument('customer'),
            employeeId: $employeeId,
            branchId: (int) $this->argument('branch'),
            items: [new \App\Domain\Sales\DTOs\SaleLineData(
                (int) $this->argument('product'),
                (int) $this->argument('quantity'),
            )],
        );

        try {
            $sale = $createSale->execute($data);
            $this->line("OK:{$sale->invoice_number}");

            return self::SUCCESS;
        } catch (InsufficientStockException) {
            $this->line('FAIL:InsufficientStock');

            return self::FAILURE;
        }
    }
}
