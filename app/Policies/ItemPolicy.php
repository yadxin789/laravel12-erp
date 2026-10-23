<?php

namespace App\Policies;

use App\Models\MasterData\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view items');
    }

    public function view(User $user, Item $item)
    {
        return $user->hasPermissionTo('view items');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create items');
    }

    public function update(User $user, Item $item)
    {
        return $user->hasPermissionTo('edit items');
    }

    public function delete(User $user, Item $item)
    {
        return $user->hasPermissionTo('delete items');
    }

    /**
     * 上传 / 删除物料规格书。
     * 采购等业务角色只有 view items 权限，也需要维护规格书。
     */
    public function uploadSpecSheet(User $user, Item $item)
    {
        return $user->hasPermissionTo('view items');
    }
}
