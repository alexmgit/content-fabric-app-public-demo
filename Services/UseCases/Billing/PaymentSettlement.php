<?php

namespace App\Services\UseCases\Billing;

use App\Contracts\Logger;
use App\Contracts\TransactionManager;
use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\TransactionDirection;
use App\Enums\Billing\TransactionType;
use App\Enums\Billing\YooKassaPaymentStatus;
use App\Models\Billing\Payment;
use App\Models\Billing\PaymentMethod;
use App\Models\Billing\Transaction;
use YooKassa\Client;
use YooKassa\Model\Payment\PaymentMethod\AbstractPaymentMethod;

class PaymentSettlement
{
    public function __construct(
        private Logger $logger,
        private TransactionManager $tx,
    ) {}

    public function handle(Payment $payment, Client $client): void
    {
        $this->logger->info('Payment process: ' . $payment->id);

        if ($payment->status !== PaymentStatus::PROCESSING->value) {
            $this->logger->info('Payment process: ' . $payment->id . ' already processed');
            return;
        }

        $response = $client->getPaymentInfo($payment->payment_id);

        if ($response->getStatus() === YooKassaPaymentStatus::SUCCEEDED->value) {
            $this->processSucceeded($payment, $response);
            return;
        }

        if ($response->getStatus() === YooKassaPaymentStatus::CANCELED->value) {
            $this->processCanceled($payment);
        }
    }

    private function processSucceeded(Payment $payment, $response): void
    {
        $this->logger->info('Payment process: ' . $payment->id . ' succeeded');

        $this->tx->transaction(function () use ($payment, $response) {
            $this->createTransaction($payment);
            $payment->update(['status' => PaymentStatus::PAID->value]);

            $paymentMethod = $response->getPaymentMethod();
            $this->persistPaymentMethod($payment, $paymentMethod);
        });
    }

    private function processCanceled(Payment $payment): void
    {
        $this->logger->info('Payment process: ' . $payment->id . ' canceled');

        $payment->update([
            'status' => PaymentStatus::FAILED->value,
        ]);
    }

    private function createTransaction(Payment $payment): void
    {
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

    private function persistPaymentMethod(Payment $payment, $paymentMethod): void
    {
        if (!$paymentMethod instanceof AbstractPaymentMethod) {
            return;
        }

        if (!$paymentMethod->getSaved() || !$payment->confirmation_token) {
            return;
        }

        PaymentMethod::where('user_id', $payment->user_id)->update(['is_default' => false]);

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
}
