<?php

namespace App\Console\Commands;

use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Events\CustomerMarkedLost;
use App\Domain\Crm\Models\Customer;
use App\Domain\Support\Repositories\SettingsRepository;
use Illuminate\Console\Command;

class DetectLostCustomers extends Command
{
    protected $signature = 'customers:detect-lost';

    protected $description = 'Flag active customers with no purchase within the configurable threshold as lost.';

    public function handle(SettingsRepository $settings): int
    {
        $days = (int) $settings->get('lost_customer_threshold_days', config('crm.lost_threshold_days', 90));
        $cutoff = now()->subDays($days);
        $count = 0;

        Customer::query()
            ->where('status', CustomerStatus::Active->value)
            ->whereNotNull('last_purchase_at')
            ->where('last_purchase_at', '<', $cutoff)
            ->chunkById(500, function ($customers) use (&$count) {
                foreach ($customers as $customer) {
                    $customer->update(['status' => CustomerStatus::Lost]);
                    CustomerMarkedLost::dispatch($customer);
                    $count++;
                }
            });

        $this->info("Marked {$count} customer(s) as lost (threshold: {$days} days).");

        return self::SUCCESS;
    }
}
