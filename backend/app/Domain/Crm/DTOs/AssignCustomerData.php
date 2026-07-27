<?php

namespace App\Domain\Crm\DTOs;

final class AssignCustomerData
{
    public function __construct(
        public readonly int $customerId,
        public readonly int $employeeId,
        public readonly int $assignedBy,
    ) {}
}
