<?php

namespace App\Services\Plan;

use App\Models\User;

class PlanManagerFactory
{
    public function forUser(User $user): PlanManager
    {
        return new PlanManager($user);
    }
}
