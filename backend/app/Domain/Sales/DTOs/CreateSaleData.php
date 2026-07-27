<?php

namespace App\Domain\Sales\DTOs;

final class CreateSaleData
{
    /**
     * @param  SaleLineData[]  $items
     */
    public function __construct(
        public readonly int $customerId,
        public readonly int $employeeId,
        public readonly int $branchId,
        public readonly array $items,
    ) {}

    /**
     * @return int[]
     */
    public function productIds(): array
    {
        return array_values(array_unique(array_map(
            fn (SaleLineData $item) => $item->productId,
            $this->items,
        )));
    }

    /**
     * Build from validated request data:
     *   ['customer_id'=>.., 'employee_id'=>.., 'branch_id'=>.., 'items'=>[['product_id'=>..,'quantity'=>..], ..]]
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customerId: (int) $data['customer_id'],
            employeeId: (int) $data['employee_id'],
            branchId: (int) $data['branch_id'],
            items: array_map(
                fn (array $line) => new SaleLineData((int) $line['product_id'], (int) $line['quantity']),
                $data['items'],
            ),
        );
    }
}
