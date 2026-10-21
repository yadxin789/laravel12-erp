<?php

namespace App\Policies;

use App\Models\MasterData\Warehouse;
use App\Models\User;

class WarehousePolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view warehouses');
    }

    public function view(User $user, Warehouse $warehouse)
    {
        return $user->hasPermissionTo('view warehouses');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create warehouses');
    }

    public function update(User $user, Warehouse $warehouse)
    {
        return $user->hasPermissionTo('edit warehouses');
    }

    public function delete(User $user, Warehouse $warehouse)
    {
        return $user->hasPermissionTo('delete warehouses');
    }
}
