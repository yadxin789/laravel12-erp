<?php

namespace App\Policies;

use App\Models\MasterData\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view customers');
    }

    public function view(User $user, Customer $customer)
    {
        return $user->hasPermissionTo('view customers');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create customers');
    }

    public function update(User $user, Customer $customer)
    {
        return $user->hasPermissionTo('edit customers');
    }

    public function delete(User $user, Customer $customer)
    {
        return $user->hasPermissionTo('delete customers');
    }
}
