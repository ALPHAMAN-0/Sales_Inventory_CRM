<?php

namespace App\Domain\Inventory\Enums;

/**
 * Every row in the stock_movements ledger carries one of these types.
 * (`Returned` backs the value 'return' — 'return' is a reserved word and
 * can't be a case name.)
 */
enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case Returned = 'return';

    /**
     * The natural sign of a movement's effect on quantity. Adjustments carry
     * an explicit signed quantity, so they return 0 here (caller supplies it).
     */
    public function sign(): int
    {
        return match ($this) {
            self::Purchase, self::Returned => 1,
            self::Sale => -1,
            self::Adjustment => 0,
        };
    }
}
