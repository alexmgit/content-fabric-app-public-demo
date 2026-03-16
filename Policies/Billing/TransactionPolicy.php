<?php

namespace App\Policies\Billing;

use App\Models\Billing\Transaction;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;

class TransactionPolicy
{
    use ChecksOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $this->isOwner($user, $transaction);
    }
}
