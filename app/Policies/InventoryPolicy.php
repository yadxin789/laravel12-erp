<?php

namespace App\Policies;

use App\Models\Inventory\InventoryBalance;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view inventory');
    }

    public function view(User $user, InventoryBalance $balance)
    {
        return $user->hasPermissionTo('view inventory');
    }

    public function receive(User $user)
    {
        return $user->hasPermissionTo('manage inventory');
    }

    public function issue(User $user)
    {
        return $user->hasPermissionTo('manage inventory');
    }

    public function transfer(User $user)
    {
        return $user->hasPermissionTo('transfer inventory');
    }

    public function adjust(User $user)
    {
        return $user->hasPermissionTo('adjust inventory');
    }

    public function freeze(User $user)
    {
        return $user->hasPermissionTo('freeze inventory');
    }
}
