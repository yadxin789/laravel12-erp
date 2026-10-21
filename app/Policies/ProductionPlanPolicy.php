<?php

namespace App\Policies;

use App\Models\Production\ProductionPlan;
use App\Models\User;

class ProductionPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'production_manager', 'warehouse_manager', 'quality_manager', 'accountant', 'viewer']);
    }

    public function view(User $user, ProductionPlan $plan): bool
    {
        return $user->hasAnyRole(['admin', 'production_manager', 'warehouse_manager', 'quality_manager', 'accountant', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'production_manager']);
    }

    public function update(User $user, ProductionPlan $plan): bool
    {
        return $user->hasAnyRole(['admin', 'production_manager']);
    }

    public function delete(User $user, ProductionPlan $plan): bool
    {
        return $user->hasAnyRole(['admin', 'production_manager']);
    }

    public function approve(User $user, ProductionPlan $plan): bool
    {
        return $user->hasAnyRole(['admin', 'production_manager']);
    }
}
