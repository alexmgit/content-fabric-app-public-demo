<?php

namespace App\Repositories\Billing;

use App\Models\Billing\Payment;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository
{
    public function getAllForUser(int $userId): Collection
    {
        return Payment::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->get();
    }

    public function findByUuidForUserOrFail(string $uuid, int $userId): Payment
    {
        return Payment::query()
            ->where('uuid', $uuid)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
