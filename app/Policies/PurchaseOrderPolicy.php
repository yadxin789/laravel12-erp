<?php

namespace App\Policies;

use App\Models\Purchasing\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view purchase orders');
    }

    public function view(User $user, PurchaseOrder $order)
    {
        return $user->hasPermissionTo('view purchase orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create purchase orders');
    }

    public function update(User $user, PurchaseOrder $order)
    {
        return $user->hasPermissionTo('edit purchase orders');
    }

    public function submit(User $user, PurchaseOrder $order)
    {
        return $user->hasPermissionTo('submit purchase orders');
    }

    public function approve(User $user, PurchaseOrder $order)
    {
        return $user->hasPermissionTo('approve purchase orders');
    }

    public function cancel(User $user, PurchaseOrder $order)
    {
        return $user->hasPermissionTo('cancel purchase orders');
    }
}
