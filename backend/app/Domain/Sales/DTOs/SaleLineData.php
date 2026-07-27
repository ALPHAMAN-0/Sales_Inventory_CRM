<?php

namespace App\Domain\Sales\DTOs;

final class SaleLineData
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
    ) {}
}
