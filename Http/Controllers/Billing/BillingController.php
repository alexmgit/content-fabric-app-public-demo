<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\TransactionResource;
use App\Policies\Billing\TransactionPolicy;
use App\Repositories\Billing\TransactionRepository;
use App\Services\Plan\PlanManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function dashboard(
        Request $request,
        TransactionRepository $transactionRepository,
    ): Response {
        $user = $request->user();
        $this->authorizePolicy(TransactionPolicy::class, 'viewAny', $user);
        $planManager = new PlanManager($user);
        $lastTransactions = $transactionRepository->getLatestForUser($user->id);

        return Inertia::render('Billing/Dashboard', [
            'current_subscription_data' => $planManager->getCurrentSubscriptionData(),
            'last_transactions' => TransactionResource::collection($lastTransactions),
        ]);
    }
}
