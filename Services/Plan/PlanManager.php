<?php

namespace App\Services\Plan;

use App\Models\User;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;

class PlanManager
{
    public readonly string $trial_plan_slug;
    public readonly string $trial_plan_interval;
    public readonly int $trial_plan_duration;
    public readonly array $promocodes;
    
    public function __construct(
        protected User $user,
    ) {
        $this->trial_plan_slug = config('subscriptions.trial_plan_slug', 'bazovyi');
        $this->trial_plan_interval = config('subscriptions.trial_plan_interval', 'day');
        $this->trial_plan_duration = config('subscriptions.trial_plan_duration', 3);
        $this->promocodes = config('services.promocodes.promocodes', []);
    }

    public function getCurrentSubscriptionData(): ?array
    {
        return [
            'subscription' => $this->getCurrentSubscription(),
            'attributes' => $this->getCurrentSubscriptionAttributes(),
            'plan' => $this->getCurrentPlan(),
        ];
    }

    public function getCurrentSubscription(): ?Subscription
    {
        return $this->user->planSubscription('main');
    }

    public function getCurrentSubscriptionAttributes(): ?array
    {
        $subscription = $this->getCurrentSubscription();

        return [
            'active' => $subscription?->active(),
            'canceled' => $subscription?->canceled(),
            'ended' => $subscription?->ended(),
            'on_trial' => $subscription?->onTrial(),
        ];
    }

    public function getCurrentPlan(): ?Plan
    {
        return $this->user->planSubscription('main')?->plan;
    }

    public function allowTrial(Plan $plan): bool
    {
        return $plan->slug === $this->trial_plan_slug && $this->user->allow_trial;
    }

    public function getPromocodeDiscount(string $promocode, float|int $price): ?array
    {
        foreach ($this->promocodes as $value) {
            $discount = $this->resolvePromocodeDiscount($value, $promocode, $price);

            if ($discount !== null) {
                return $discount;
            }
        }

        return null;
    }

    public function applyPromocode(Plan $plan, string $promocode): ?array
    {
        $discount = $this->getPromocodeDiscount($promocode, $plan->price);

        if ($discount !== null) {
            $plan->price -= $discount['diff'];
        }

        return $discount;
    }

    private function resolvePromocodeDiscount(array $value, string $promocode, float|int $price): ?array
    {
        if (count($value) !== 2) {
            return null;
        }

        [$code, $rawValue] = $value;

        if (strcasecmp($code, $promocode) !== 0) {
            return null;
        }

        if (stripos($rawValue, '%') !== false) {
            $percent = (int) $rawValue;

            return [
                'apply' => true,
                'diff' => $price / 100 * $percent,
            ];
        }

        if (stripos($rawValue, 'ABS') !== false) {
            return [
                'apply' => true,
                'diff' => (int) $rawValue,
            ];
        }

        return null;
    }
}
