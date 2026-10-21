<?php

namespace App\Policies;

use App\Models\Sales\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view sales orders');
    }

    public function view(User $user, SalesOrder $order)
    {
        return $user->hasPermissionTo('view sales orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create sales orders');
    }

    public function update(User $user, SalesOrder $order)
    {
        return $user->hasPermissionTo('edit sales orders');
    }

    public function submit(User $user, SalesOrder $order)
    {
        return $user->hasPermissionTo('submit sales orders');
    }

    public function approve(User $user, SalesOrder $order)
    {
        return $user->hasPermissionTo('approve sales orders');
    }

    public function cancel(User $user, SalesOrder $order)
    {
        return $user->hasPermissionTo('cancel sales orders');
    }

    public function export(User $user, SalesOrder $order)
    {
        return $user->hasPermissionTo('export sales orders');
    }
}
