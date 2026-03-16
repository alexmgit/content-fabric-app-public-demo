<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\TransactionResource;
use App\Policies\Billing\TransactionPolicy;
use App\Repositories\Billing\TransactionRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(
        Request $request,
        TransactionRepository $transactionRepository,
    ): Response {
        $user = $request->user();
        $this->authorizePolicy(TransactionPolicy::class, 'viewAny', $user);
        $transactions = $transactionRepository->getAllForUser($user->id);

        return Inertia::render('Billing/Transactions/Index', [
            'transactions' => TransactionResource::collection($transactions),
        ]);
    }
}
