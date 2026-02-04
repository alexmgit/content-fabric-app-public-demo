<?php

namespace App\Services\Adapters;

use App\Contracts\FeatureFlags;

class ConfigFeatureFlags implements FeatureFlags
{
    public function usePlans(): bool
    {
        return (bool) config('app.is_use_plans');
    }
}
