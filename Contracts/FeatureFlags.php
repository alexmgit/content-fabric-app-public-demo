<?php

namespace App\Contracts;

interface FeatureFlags
{
    public function usePlans(): bool;
}
