<?php

namespace App\Http\Resources\Billing;

class CurrentSubscriptionDataResource
{
    public static function make(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return [
            'subscription' => CurrentSubscriptionResource::make($data['subscription'] ?? null),
            'attributes' => $data['attributes'] ?? null,
            'plan' => isset($data['plan']) ? PlanResource::make($data['plan']) : null,
        ];
    }
}
