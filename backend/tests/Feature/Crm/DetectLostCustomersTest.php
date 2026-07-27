<?php

use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Support\Repositories\SettingsRepository;

it('flags exactly the active customers past the configured threshold', function () {
    $stale = Customer::factory()->create([
        'status' => CustomerStatus::Active,
        'last_purchase_at' => now()->subDays(91),
    ]);
    $fresh = Customer::factory()->create([
        'status' => CustomerStatus::Active,
        'last_purchase_at' => now()->subDays(89),
    ]);
    $neverBought = Customer::factory()->create([
        'status' => CustomerStatus::Active,
        'last_purchase_at' => null,
    ]);

    $this->artisan('customers:detect-lost')->assertSuccessful();

    expect($stale->fresh()->status)->toBe(CustomerStatus::Lost)
        ->and($fresh->fresh()->status)->toBe(CustomerStatus::Active)
        ->and($neverBought->fresh()->status)->toBe(CustomerStatus::Active);
});

it('honors the runtime settings override before the config default', function () {
    // With the config default (90) a 45-day-old customer would stay active;
    // the runtime override (30) must flag it.
    app(SettingsRepository::class)->set('lost_customer_threshold_days', 30);

    $customer = Customer::factory()->create([
        'status' => CustomerStatus::Active,
        'last_purchase_at' => now()->subDays(45),
    ]);

    $this->artisan('customers:detect-lost')->assertSuccessful();

    expect($customer->fresh()->status)->toBe(CustomerStatus::Lost);
});
