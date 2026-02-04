<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Plan\PlanManager;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Billing\Transaction;
use App\Enums\Billing\TransactionType;

class BillingController extends Controller
{
    public function dashboard() {
        $planManager = new PlanManager(Auth::user());

        $last_transactions = Transaction::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $last_transactions->transform(function ($transaction) {
            $transaction->type_label = TransactionType::from($transaction->type)->label();
            return $transaction;
        });

        return Inertia::render('Billing/Dashboard', [
            'current_subscription_data' => $planManager->getCurrentSubscriptionData(),
            'last_transactions' => $last_transactions,
        ]);
    }
}
