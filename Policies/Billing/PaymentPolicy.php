<?php

namespace App\Policies\Billing;

use App\Models\Billing\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;

class PaymentPolicy
{
    use ChecksOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->isOwner($user, $payment);
    }

    public function create(User $user): bool
    {
        return true;
    }
}
