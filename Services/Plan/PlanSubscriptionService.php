<?php

namespace App\Services\Plan;

use App\Enums\Billing\TransactionDirection;
use App\Enums\Billing\TransactionType;
use App\Models\Billing\Transaction;
use App\Models\Billing\UsePromocode;
use App\Models\User;
use App\Notifications\PlanApply;
use Illuminate\Support\Facades\DB;
use Laravelcm\Subscriptions\Models\Plan;
use RuntimeException;

class PlanSubscriptionService
{
    public function __construct(
        private readonly PlanManagerFactory $planManagerFactory,
    ) {
    }

    public function subscribe(User $user, Plan $plan, ?string $promocode = null): Plan
    {
        $planManager = $this->planManagerFactory->forUser($user);
        $allowTrial = $planManager->allowTrial($plan);
        $promocodeResult = null;

        if ($user->planSubscription('main')) {
            throw new RuntimeException('У вас уже есть активный тариф. Обратитесь, пожалуйста, в службу поддержки если вы хотите сменить тариф');
        }

        if ($allowTrial) {
            $plan->trial_period = $planManager->trial_plan_duration;
            $plan->trial_interval = $planManager->trial_plan_interval;
        } else {
            $plan->trial_period = 0;

            if ($promocode) {
                $promocodeResult = $planManager->applyPromocode($plan, $promocode);
            }

            if (($user->balance - $plan->price) < 0) {
                throw new RuntimeException('Недостаточно средств для подключения тарифа');
            }
        }

        DB::transaction(function () use ($user, $plan, $allowTrial, $promocode, $promocodeResult) {
            $user->newPlanSubscription('main', $plan);

            if ($allowTrial) {
                return;
            }

            Transaction::create([
                'user_id' => $user->id,
                'team_id' => $user->current_team_id,
                'amount' => -$plan->price,
                'currency' => 'RUB',
                'description' => 'Подключение тарифа ' . $plan->name . ($promocodeResult ? ' с промокодом ' . $promocode : ''),
                'type' => TransactionType::PLAN_PAYMENT->value,
                'direction' => TransactionDirection::OUT->value,
            ]);

            if ($promocodeResult) {
                UsePromocode::create([
                    'user_id' => $user->id,
                    'team_id' => $user->current_team_id,
                    'promocode' => $promocode,
                ]);
            }
        });

        $user->notify(new PlanApply($plan));

        return $plan;
    }
}
