<?php

namespace App\Services\Payment;

use App\Enums\Billing\PaymentStatus;
use App\Models\Billing\Payment;

class PaymentStatusService
{
    public function markAsProcessingIfWaiting(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::WAITING->value) {
            return $payment;
        }

        $payment->update([
            'status' => PaymentStatus::PROCESSING->value,
        ]);

        return $payment->fresh();
    }
}
