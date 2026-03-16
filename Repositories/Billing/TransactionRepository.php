<?php

namespace App\Repositories\Billing;

use App\Models\Billing\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{
    public function getLatestForUser(int $userId, int $limit = 10): Collection
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getAllForUser(int $userId): Collection
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->get();
    }
}
