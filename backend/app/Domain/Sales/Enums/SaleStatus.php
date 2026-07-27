<?php

namespace App\Domain\Sales\Enums;

enum SaleStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
