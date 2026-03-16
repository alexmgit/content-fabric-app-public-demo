<?php

namespace App\Services\Plan;

use App\Models\Billing\UsePromocode;
use App\Models\User;
use Laravelcm\Subscriptions\Models\Plan;

class PlanPreviewService
{
    public function __construct(
        private readonly PlanManagerFactory $planManagerFactory,
    ) {
    }

    public function build(User $user, Plan $plan, bool $isTrial = false, ?string $promocode = null): array
    {
        $planManager = $this->planManagerFactory->forUser($user);
        $promocodeModel = null;

        if ($isTrial && $planManager->allowTrial($plan)) {
            $plan->trial_period = $planManager->trial_plan_duration;
            $plan->trial_interval = $planManager->trial_plan_interval;

            return [
                'promocode' => null,
                'plan' => $plan,
                'user' => [
                    'can_subscribe' => true,
                    'money_left' => 0,
                ],
                'current_plan' => $planManager->getCurrentPlan(),
            ];
        }

        if ($promocode) {
            if (UsePromocode::query()
                ->where('user_id', $user->id)
                ->where('promocode', $promocode)
                ->exists()) {
                $promocodeModel = [
                    'success' => false,
                    'message' => 'Промокод уже использован',
                ];
            } else {
                $promocodeResult = $planManager->applyPromocode($plan, $promocode);

                $promocodeModel = [
                    'success' => $promocodeResult !== null,
                    'message' => $promocodeResult ? 'Промокод применён' : 'Промокод не найден',
                ];
            }
        }

        $moneyLeft = $user->balance - $plan->price;

        return [
            'promocode' => $promocodeModel,
            'plan' => $plan,
            'user' => [
                'can_subscribe' => $moneyLeft >= 0,
                'money_left' => $moneyLeft,
            ],
            'current_plan' => $planManager->getCurrentPlan(),
        ];
    }
}
