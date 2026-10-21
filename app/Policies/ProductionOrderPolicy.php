<?php

namespace App\Policies;

use App\Models\Production\ProductionOrder;
use App\Models\User;

class ProductionOrderPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view production orders');
    }

    public function view(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('view production orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create production orders');
    }

    public function update(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('edit production orders');
    }

    public function release(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('release production orders');
    }

    public function start(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('start production orders');
    }

    public function complete(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('complete production orders');
    }

    public function close(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('close production orders');
    }

    public function cancel(User $user, ProductionOrder $order)
    {
        return $user->hasPermissionTo('cancel production orders');
    }
}
