<?php

namespace App\Policies;

use App\Models\MasterData\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view suppliers');
    }

    public function view(User $user, Supplier $supplier)
    {
        return $user->hasPermissionTo('view suppliers');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create suppliers');
    }

    public function update(User $user, Supplier $supplier)
    {
        return $user->hasPermissionTo('edit suppliers');
    }

    public function delete(User $user, Supplier $supplier)
    {
        return $user->hasPermissionTo('delete suppliers');
    }
}
