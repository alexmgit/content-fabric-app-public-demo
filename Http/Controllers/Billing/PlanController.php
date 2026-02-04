<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravelcm\Subscriptions\Models\Plan as SubscriptionPlan;
use App\Services\Plan\PlanManager;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Billing\Transaction;
use App\Enums\Billing\TransactionType;
use App\Enums\Billing\TransactionDirection;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\PlanApply;
use App\Models\Billing\UsePromocode;
use Illuminate\Support\Facades\Notification;

class PlanController extends Controller
{
    public function index() {
        $planManager = new PlanManager(Auth::user());

        $plans = SubscriptionPlan::where('is_active', true)
            ->with([
                'features' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ])
            ->get()
            ->transform(function ($plan) use ($planManager) {
                $plan->allow_trial = $planManager->allowTrial($plan);
                return $plan;
            });

        $planManager = new PlanManager(Auth::user());

        return Inertia::render('Billing/Plans/Index', [
            'plans' => $plans,
            'current_subscription_data' => $planManager->getCurrentSubscriptionData(),
            'trial_period' => $planManager->trial_plan_duration,
            'trial_interval' => $planManager->trial_plan_interval,
            'search_enabled' => config('services.searches.enabled'),
        ]);
    }

    public function plans() {
        $planManager = new PlanManager(Auth::user());

        $plans = SubscriptionPlan::where('is_active', true)
            ->with([
                'features' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ])
            ->get()
            ->transform(function ($plan) use ($planManager) {
                $plan->allow_trial = $planManager->allowTrial($plan);
                return $plan;
            });

        return response()->json([
            'plans' => $plans,
        ]);
    }

    public function data() {
        /** @var SubscriptionPlan $plan */
        $plan = SubscriptionPlan::where('is_active', true)->find(request('plan'));
        $planManager = new PlanManager(Auth::user());
        $is_trial = request('is_trial');
        $promocode = request('promocode');
        $user = Auth::user();
        $promocodeModel = null;

        if ($is_trial && $planManager->allowTrial($plan)) {
            $can_subscribe = true;
            $money_left = 0;
            $plan->trial_period = $planManager->trial_plan_duration;
            $plan->trial_interval = $planManager->trial_plan_interval;
        } else {    
            if ($promocode)
            {
                if (UsePromocode::where('user_id', $user->id)->where('promocode', $promocode)->exists())
                {
                    $promocodeModel = [
                        'success' => false,
                        'message' => 'Промокод уже использован',
                    ];
                }
                else
                {
                    $promocodeResult = $planManager->applyPromocode($plan, $promocode);

                    if ($promocodeResult)
                    {
                        $promocodeModel = [
                            'success' => true,
                            'message' => 'Промокод применён',
                        ];
                    }
                    else
                    {
                        $promocodeModel = [
                            'success' => false,
                            'message' => 'Промокод не найден',
                        ];
                    }
                }
            }

            $can_subscribe = $user->balance >= $plan->price;
            $money_left = $user->balance - $plan->price;
        }

        $planManager = new PlanManager($user);

        return response()->json([
            'promocode' => $promocodeModel,
            'plan' => $plan,
            'user' => [
                'can_subscribe' => $can_subscribe,
                'money_left' => $money_left,
            ],
            'current_plan' => $planManager->getCurrentPlan(),
        ]);
    }

    public function subscribe() {
        /** @var SubscriptionPlan $plan */
        $plan = SubscriptionPlan::where('is_active', true)->find(request('plan'));
        $planManager = new PlanManager(Auth::user());
        // $is_trial = request('is_trial');
        /** @var User $user */
        $user = Auth::user();
        $allowTrial = $planManager->allowTrial($plan);
        $promocode = request('promocode');
        $promocodeResult = null;

        $current_plan = $user->planSubscription('main');

        if ($current_plan) {
            return redirect()->route('billing.plans.index')
                ->dangerBanner('У вас уже есть активный тариф. Обратитесь, пожалуйста, в службу поддержки если вы хотите сменить тариф');
        }

        if ($allowTrial) {
            $plan->trial_period = $planManager->trial_plan_duration;
            $plan->trial_interval = $planManager->trial_plan_interval;
        } else {
            $plan->trial_period = 0;

            if ($promocode)
            {
                $promocodeResult = $planManager->applyPromocode($plan, $promocode);
            }

            $money_left = $user->balance - $plan->price;
            if ($money_left < 0) {
                return redirect()->route('billing.plans.index')
                    ->dangerBanner('Недостаточно средств для подключения тарифа');
            }
        }

        try {
            DB::beginTransaction();

            $user->newPlanSubscription('main', $plan);

            if ($allowTrial) {
                
            } else {
                Transaction::create([
                    'user_id' => $user->id,
                    'team_id' => $user->current_team_id,
                    'amount' => -$plan->price,
                    'currency' => 'RUB',
                    'description' => 'Подключение тарифа ' . $plan->name . ($promocodeResult ? ' с промокодом ' . $promocode : ''),
                    'type' => TransactionType::PLAN_PAYMENT->value,
                    'direction' => TransactionDirection::OUT->value,
                ]);

                if ($promocodeResult)
                {
                    UsePromocode::create([
                        'user_id' => $user->id,
                        'team_id' => $user->current_team_id,
                        'promocode' => $promocode,
                    ]);
                }
            }

            DB::commit();

            $user->notify(new PlanApply($plan));

            return redirect()->route('trends.sources.index')->banner('Тариф ' . $plan->name . ' подключен');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Не удалось подключить тариф',
            ], 400);
        }
    }

    public function cancel() {
        $user = Auth::user();
        $planManager = new PlanManager($user);
        $current_subscription = $planManager->getCurrentSubscription();

        if ($current_subscription) {
            $current_subscription->cancel();
        }

        return redirect()->route('billing.dashboard')->banner('Подписка отменена');
    }
}
