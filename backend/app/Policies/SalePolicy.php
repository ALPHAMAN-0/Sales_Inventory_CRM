<?php

namespace App\Policies;

use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated staff member (admin or employee) may record a sale.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'employee']);
    }
}
