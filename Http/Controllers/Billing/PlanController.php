<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\PlanDataRequest;
use App\Http\Requests\Billing\SubscribePlanRequest;
use App\Http\Resources\Billing\CurrentSubscriptionDataResource;
use App\Http\Resources\Billing\PlanResource;
use App\Repositories\Billing\SubscriptionPlanRepository;
use App\Services\Plan\PlanPreviewService;
use App\Services\Plan\PlanManager;
use App\Services\Plan\PlanSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class PlanController extends Controller
{
    public function index(
        Request $request,
        SubscriptionPlanRepository $subscriptionPlanRepository,
    ): Response
    {
        $user = $request->user();
        $planManager = new PlanManager($user);
        $plans = $this->transformPlans($subscriptionPlanRepository->getActiveWithFeatures(), $planManager);

        return Inertia::render('Billing/Plans/Index', [
            'plans' => PlanResource::collection($plans),
            'current_subscription_data' => CurrentSubscriptionDataResource::make($planManager->getCurrentSubscriptionData()),
            'trial_period' => $planManager->trial_plan_duration,
            'trial_interval' => $planManager->trial_plan_interval,
            'search_enabled' => config('services.searches.enabled'),
        ]);
    }

    public function plans(
        Request $request,
        SubscriptionPlanRepository $subscriptionPlanRepository,
    ): JsonResponse
    {
        $plans = $this->transformPlans(
            $subscriptionPlanRepository->getActiveWithFeatures(),
            new PlanManager($request->user())
        );

        return response()->json([
            'plans' => PlanResource::collection($plans),
        ]);
    }

    public function data(
        PlanDataRequest $request,
        SubscriptionPlanRepository $subscriptionPlanRepository,
        PlanPreviewService $planPreviewService,
    ): JsonResponse {
        $validated = $request->validated();
        $plan = $subscriptionPlanRepository->findActiveOrFail($validated['plan']);

        $preview = $planPreviewService->build(
            $request->user(),
            $plan,
            (bool) ($validated['is_trial'] ?? false),
            $validated['promocode'] ?? null,
        );

        return response()->json([
            'promocode' => $preview['promocode'],
            'plan' => PlanResource::make($preview['plan']),
            'user' => $preview['user'],
            'current_plan' => isset($preview['current_plan']) ? PlanResource::make($preview['current_plan']) : null,
        ]);
    }

    public function subscribe(
        SubscribePlanRequest $request,
        SubscriptionPlanRepository $subscriptionPlanRepository,
        PlanSubscriptionService $planSubscriptionService,
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();
        $plan = $subscriptionPlanRepository->findActiveOrFail($validated['plan']);

        try {
            $planSubscriptionService->subscribe(
                $request->user(),
                $plan,
                $validated['promocode'] ?? null,
            );
            return redirect()->route('trends.sources.index')->banner('Тариф ' . $plan->name . ' подключен');
        } catch (RuntimeException $exception) {
            return redirect()->route('billing.plans.index')
                ->dangerBanner($exception->getMessage());
        } catch (\Throwable $exception) {
            return response()->json([
                'error' => 'Не удалось подключить тариф',
            ], 400);
        }
    }

    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();
        $planManager = new PlanManager($user);
        $current_subscription = $planManager->getCurrentSubscription();

        if ($current_subscription) {
            $current_subscription->cancel();
        }

        return redirect()->route('billing.dashboard')->banner('Подписка отменена');
    }

    private function transformPlans(iterable $plans, PlanManager $planManager): iterable
    {
        foreach ($plans as $plan) {
            $plan->allow_trial = $planManager->allowTrial($plan);
        }

        return $plans;
    }
}
