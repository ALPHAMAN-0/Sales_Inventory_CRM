<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only admins may assign lost customers to employees for recovery.
     */
    public function assign(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
