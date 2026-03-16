<?php

namespace App\Policies\Billing;

use App\Models\Billing\PaymentMethod;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;

class PaymentMethodPolicy
{
    use ChecksOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->isOwner($user, $paymentMethod);
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->isOwner($user, $paymentMethod);
    }
}
