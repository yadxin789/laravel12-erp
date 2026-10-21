<?php

namespace App\Policies;

use App\Models\Quality\QualityInspection;
use App\Models\User;

class QualityPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view quality inspections');
    }

    public function view(User $user, QualityInspection $inspection)
    {
        return $user->hasPermissionTo('view quality inspections');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create quality inspections');
    }

    public function update(User $user, QualityInspection $inspection)
    {
        return $user->hasPermissionTo('edit quality inspections');
    }

    public function approve(User $user, QualityInspection $inspection)
    {
        return $user->hasPermissionTo('complete quality inspections');
    }
}
