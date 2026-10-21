<?php

namespace App\Policies;

use App\Models\Production\BomHeader;
use App\Models\User;

class BomPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view boms');
    }

    public function view(User $user, BomHeader $bom)
    {
        return $user->hasPermissionTo('view boms');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create boms');
    }

    public function update(User $user, BomHeader $bom)
    {
        return $user->hasPermissionTo('edit boms');
    }

    public function approve(User $user, BomHeader $bom)
    {
        return $user->hasPermissionTo('approve boms');
    }

    public function delete(User $user, BomHeader $bom)
    {
        return $user->hasPermissionTo('delete boms');
    }
}
