<?php

namespace App\Http\Resources\Billing;

use Laravelcm\Subscriptions\Models\Subscription;

class CurrentSubscriptionResource
{
    public static function make(?Subscription $subscription): ?array
    {
        if ($subscription === null) {
            return null;
        }

        return [
            'id' => $subscription->id,
            'name' => $subscription->name ?? null,
            'slug' => $subscription->slug ?? null,
            'starts_at' => $subscription->starts_at ?? null,
            'ends_at' => $subscription->ends_at ?? null,
            'cancels_at' => $subscription->cancels_at ?? null,
            'canceled_at' => $subscription->canceled_at ?? null,
        ];
    }
}
