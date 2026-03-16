<?php

namespace App\Http\Resources\Billing;

use Laravelcm\Subscriptions\Models\Plan;

class PlanResource
{
    public static function collection(iterable $plans): array
    {
        $items = [];

        foreach ($plans as $plan) {
            $items[] = self::make($plan);
        }

        return $items;
    }

    public static function make(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'slug' => $plan->slug ?? null,
            'name' => $plan->name ?? null,
            'description' => $plan->description ?? null,
            'price' => $plan->price ?? null,
            'signup_fee' => $plan->signup_fee ?? null,
            'currency' => $plan->currency ?? null,
            'invoice_period' => $plan->invoice_period ?? null,
            'invoice_interval' => $plan->invoice_interval ?? null,
            'trial_period' => $plan->trial_period ?? null,
            'trial_interval' => $plan->trial_interval ?? null,
            'sort_order' => $plan->sort_order ?? null,
            'is_active' => (bool) ($plan->is_active ?? false),
            'allow_trial' => (bool) ($plan->allow_trial ?? false),
            'features' => PlanFeatureResource::collection($plan->features ?? []),
        ];
    }
}
