<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;

trait ChecksPlanFeature
{
    private function denyIfReachedFeatureLimit(User $user, int $count, string $feature, string $message): Response
    {
        if (! config('app.is_use_plans')) {
            return Response::allow();
        }

        $subscription = $user->planSubscription('main');
        $max = (int) ($subscription ? $subscription->getFeatureValue($feature) : 0);

        if ($count >= $max) {
            return Response::deny($message);
        }

        return Response::allow();
    }

    private function denyIfFeatureUnavailable(User $user, string $feature, string $message): Response
    {
        if (! config('app.is_use_plans')) {
            return Response::allow();
        }

        $subscription = $user->planSubscription('main');
        $max = (int) ($subscription ? $subscription->getFeatureValue($feature) : 0);

        if ($max <= 0) {
            return Response::deny($message);
        }

        return Response::allow();
    }
}
