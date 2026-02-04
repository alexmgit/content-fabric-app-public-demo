<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Billing\Transaction;
use App\Enums\Billing\TransactionType;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index() {
        $transactions = Transaction::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions->transform(function ($transaction) {
            $transaction->type_label = TransactionType::from($transaction->type)->label();
            return $transaction;
        });

        return Inertia::render('Billing/Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }
}
