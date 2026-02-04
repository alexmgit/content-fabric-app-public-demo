<?php

namespace App\Services\Plan;

use App\Models\User;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;

class PlanManager
{
    public $trial_plan_slug = 'bazovyi';
    public $trial_plan_interval = 'day';
    public $trial_plan_duration = 3;
    public $promocodes = [];
    
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

    public function applyPromocode(Plan $plan, string $promocode): ?array
    {
        foreach ($this->promocodes as $value)
        {
            if (count($value) !== 2)
            {
                continue;
            }
            list($p, $v) = $value;

            if (strcasecmp($p, $promocode) === 0)
            {
                if (stripos($v, '%') !== false)
                {
                    $v = intval($v);

                    $diff = $plan->price / 100 * $v;

                    $plan->price -= $diff;

                    return [
                        'apply' => true,
                        'diff' => $diff,
                    ];
                }
                elseif (stripos($v, 'ABS') !== false)
                {
                    $v = intval($v);

                    $diff = $v;

                    $plan->price -= $diff;

                    return [
                        'apply' => true,
                        'diff' => $diff,
                    ];
                }
            }
        }

        return null;
    }
}