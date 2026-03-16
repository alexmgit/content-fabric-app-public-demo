<?php

namespace App\Http\Resources\Billing;

use App\Enums\Billing\PaymentStatus;
use App\Models\Billing\Payment;

class PaymentResource
{
    public static function collection(iterable $payments): array
    {
        $items = [];

        foreach ($payments as $payment) {
            $items[] = self::make($payment);
        }

        return $items;
    }

    public static function make(Payment $payment): array
    {
        $status = PaymentStatus::from($payment->status);

        return [
            'id' => $payment->id,
            'uuid' => $payment->uuid,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'description' => $payment->description,
            'status' => $payment->status,
            'status_label' => $status->label(),
            'status_color' => $status->color(),
            'created_at' => $payment->created_at,
        ];
    }
}
