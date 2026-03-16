<?php

namespace App\Services\Payment;

use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\TransactionDirection;
use App\Enums\Billing\TransactionType;
use App\Models\Billing\Payment;
use App\Models\Billing\PaymentMethod;
use App\Models\Billing\Transaction;
use Illuminate\Support\Facades\DB;
use YooKassa\Client;
use YooKassa\Model\Payment\PaymentMethod\AbstractPaymentMethod;

class PaymentProcessingService
{
    public function process(Payment $payment, Client $client): void
    {
        if ($payment->status !== PaymentStatus::PROCESSING->value) {
            return;
        }

        $response = $client->getPaymentInfo($payment->payment_id);

        if ($response->getStatus() === 'succeeded') {
            $this->markAsPaid($payment, $response->getPaymentMethod());
            return;
        }

        if ($response->getStatus() === 'canceled') {
            $payment->update([
                'status' => PaymentStatus::FAILED->value,
            ]);
        }
    }

    public function processAutoPayment(Payment $payment, Client $client): bool
    {
        if ($payment->status !== PaymentStatus::WAITING->value) {
            return $payment->status === PaymentStatus::PAID->value;
        }

        $response = $client->getPaymentInfo($payment->payment_id);

        if ($response->getStatus() === 'succeeded') {
            $this->markAsPaid($payment, $response->getPaymentMethod());
            return true;
        }

        if ($response->getStatus() === 'canceled') {
            $payment->update([
                'status' => PaymentStatus::FAILED->value,
            ]);
        }

        return false;
    }

    private function markAsPaid(Payment $payment, mixed $paymentMethod = null): void
    {
        DB::transaction(function () use ($payment, $paymentMethod) {
            $payment = $payment->fresh();

            if ($payment->status === PaymentStatus::PAID->value) {
                return;
            }

            $hasTransaction = Transaction::query()
                ->where('payment_id', $payment->id)
                ->where('type', TransactionType::PAYMENT->value)
                ->exists();

            if (! $hasTransaction) {
                Transaction::create([
                    'user_id' => $payment->user_id,
                    'team_id' => $payment->team_id,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'description' => $payment->description,
                    'type' => TransactionType::PAYMENT->value,
                    'direction' => TransactionDirection::IN->value,
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::PAID->value,
            ]);

            if (
                $paymentMethod instanceof AbstractPaymentMethod
                && $paymentMethod->getSaved()
                && $payment->confirmation_token
            ) {
                PaymentMethod::query()
                    ->where('user_id', $payment->user_id)
                    ->update(['is_default' => false]);

                PaymentMethod::create([
                    'user_id' => $payment->user_id,
                    'team_id' => $payment->team_id,
                    'payment_method_id' => $paymentMethod->getId(),
                    'payment_method_title' => $paymentMethod->getTitle() ?? '',
                    'payment_method_data' => json_encode($paymentMethod->toArray()),
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }
        });
    }
}
