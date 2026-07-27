<?php

namespace App\Domain\Crm\Enums;

enum AssignmentStatus: string
{
    case Pending = 'pending';
    case Contacted = 'contacted';
    case Recovered = 'recovered';
    case Expired = 'expired';

    /**
     * An "open" assignment is one still eligible to be recovered. KPI crediting
     * only matches open assignments, then closes them — the basis of idempotency.
     */
    public function isOpen(): bool
    {
        return $this === self::Pending || $this === self::Contacted;
    }

    /** @return array<int, self> */
    public static function open(): array
    {
        return [self::Pending, self::Contacted];
    }
}
