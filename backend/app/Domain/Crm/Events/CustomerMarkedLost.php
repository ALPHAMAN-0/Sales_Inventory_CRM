<?php

namespace App\Domain\Crm\Events;

use App\Domain\Crm\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerMarkedLost
{
    use Dispatchable, SerializesModels;

    public function __construct(public Customer $customer) {}
}
