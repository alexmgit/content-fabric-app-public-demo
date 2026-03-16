<?php

namespace App\Repositories\Billing;

use App\Models\Billing\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodRepository
{
    public function getAllForUser(int $userId): Collection
    {
        return PaymentMethod::query()
            ->where('user_id', $userId)
            ->get();
    }

    public function findForUserOrFail(int $id, int $userId): PaymentMethod
    {
        return PaymentMethod::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
