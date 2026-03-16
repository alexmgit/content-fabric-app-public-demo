<?php

namespace App\Repositories\Billing;

use Illuminate\Database\Eloquent\Collection;
use Laravelcm\Subscriptions\Models\Plan;

class SubscriptionPlanRepository
{
    public function getActiveWithFeatures(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->with([
                'features' => function ($query) {
                    $query->orderBy('sort_order');
                },
            ])
            ->get();
    }

    public function findActiveOrFail(int $id): Plan
    {
        return Plan::query()
            ->where('is_active', true)
            ->findOrFail($id);
    }
}
