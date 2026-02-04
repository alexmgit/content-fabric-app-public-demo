<?php

namespace App\Services\UseCases\Billing;

use App\Contracts\Logger;
use App\Contracts\Mailer;
use App\Contracts\Sleeper;
use App\Contracts\TransactionManager;
use App\Enums\Billing\Currency;
use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\SubscriptionPaymentStatus;
use App\Enums\Billing\TransactionDirection;
use App\Enums\Billing\TransactionType;
use App\Enums\Billing\YooKassaPaymentStatus;
use App\Mail\Plan\PlanExpire;
use App\Models\Billing\PaymentMethod;
use App\Models\Billing\SubscriptionPayment;
use App\Models\Billing\Transaction;
use App\Services\Payment\PaymentManager;
use YooKassa\Client as YooKassaClient;

class SubscriptionPaymentHandler
{
    private const MAX_TRY = 5;
    private const AUTO_PAYMENT_RETRIES = 5;
    private const AUTO_PAYMENT_SLEEP = 30;

    public function __construct(
        private Logger $logger,
        private TransactionManager $tx,
        private Mailer $mailer,
        private Sleeper $sleeper,
    ) {}

    public function handle(SubscriptionPayment $subscriptionPayment, YooKassaClient $yooKassaClient): void
    {
        if ($subscriptionPayment->status !== SubscriptionPaymentStatus::WAITING->value) {
            return;
        }

        $mailTo = $subscriptionPayment->subscription->subscriber;

        try {
            $this->tx->begin();

            if ($this->hasExceededRetries($subscriptionPayment)) {
                $this->markSubscriptionFailedAndExpire($subscriptionPayment, $mailTo);
                return;
            }
           
            $money_left = $subscriptionPayment->subscription->subscriber->balance - $subscriptionPayment->subscription->plan->price;

            if ($money_left < 0) {
                $paymentMethod = $this->getDefaultPaymentMethod($subscriptionPayment);

                if (!$paymentMethod) {
                    $this->markSubscriptionFailedAndExpire($subscriptionPayment, $mailTo);
                    return;
                }

                if (!$this->tryAutoPayment($subscriptionPayment, $paymentMethod, $yooKassaClient, $money_left)) {
                    $this->incrementRetryCount($subscriptionPayment);
                    $this->tx->commit();
                    return;
                }
            }

            $this->createPlanPaymentTransaction($subscriptionPayment);
            $subscriptionPayment->update(['status' => SubscriptionPaymentStatus::PAID->value]);

            $this->logger->info('Subscription paid', ['user_id' => $subscriptionPayment->subscription->subscriber->id]);

            $this->tx->commit();
        } catch (\Exception $e) {
            $this->tx->rollBack();

            $this->incrementRetryCount($subscriptionPayment);

            $this->logger->error('Subscription pay failed', [
                'user_id' => $subscriptionPayment->subscription->subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function tryAutoPayment(
        SubscriptionPayment $subscriptionPayment,
        PaymentMethod $paymentMethod,
        YooKassaClient $yooKassaClient,
        $money_left
    ): bool {
        $payment = $subscriptionPayment->payment;
        if ($payment === null) {
            $paymentManager = new PaymentManager($yooKassaClient);

            $payment = $paymentManager->createPaymentWithToken($subscriptionPayment->subscription->subscriber, [
                'amount' => abs($money_left),
            ], $paymentMethod->payment_method_id);

            $subscriptionPayment->update([
                'payment_id' => $payment->id,
            ]);
        }

        for ($i = 0; $i < self::AUTO_PAYMENT_RETRIES; $i++) {
            $payment = $payment->fresh();

            if ($payment->status !== PaymentStatus::WAITING->value) {
                return $payment->status === PaymentStatus::PAID->value;
            }

            $response = $yooKassaClient->getPaymentInfo($payment->payment_id);
            
            if ($response->getStatus() === YooKassaPaymentStatus::SUCCEEDED->value) {
                $this->logger->info('Payment process: succeeded', ['payment_id' => $payment->id]);

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

                $payment->update([
                    'status' => PaymentStatus::PAID->value,
                ]);

                return true;
            } else if ($response->getStatus() === YooKassaPaymentStatus::CANCELED->value) {
                $this->logger->info('Payment process: ' . $payment->id . ' canceled');

                $payment->update([
                    'status' => PaymentStatus::FAILED->value,
                ]);

                return false;
            }
            
            $this->sleeper->sleep(self::AUTO_PAYMENT_SLEEP);
        }

        return false;
    }

    private function hasExceededRetries(SubscriptionPayment $subscriptionPayment): bool
    {
        return intval($subscriptionPayment->retry_count) >= self::MAX_TRY;
    }

    private function markSubscriptionFailedAndExpire(SubscriptionPayment $subscriptionPayment, $mailTo): void
    {
        $subscriptionPayment->update([
            'status' => SubscriptionPaymentStatus::FAILED->value,
        ]);

        $this->logger->info('Subscription deleted', ['user_id' => $subscriptionPayment->subscription->subscriber->id]);

        $subscriptionPayment->subscription->delete();

        $this->tx->commit();

        $this->mailer->send($mailTo, new PlanExpire());
    }

    private function getDefaultPaymentMethod(SubscriptionPayment $subscriptionPayment): ?PaymentMethod
    {
        return PaymentMethod::where('user_id', $subscriptionPayment->subscription->subscriber->id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    private function incrementRetryCount(SubscriptionPayment $subscriptionPayment): void
    {
        $subscriptionPayment->update([
            'retry_count' => $subscriptionPayment->retry_count + 1,
        ]);
    }

    private function createPlanPaymentTransaction(SubscriptionPayment $subscriptionPayment): void
    {
        Transaction::create([
            'user_id' => $subscriptionPayment->subscription->subscriber->id,
            'team_id' => $subscriptionPayment->subscription->subscriber->current_team_id,
            'amount' => -$subscriptionPayment->subscription->plan->price,
            'currency' => Currency::RUB->value,
            'description' => 'Продление тарифа ' . $subscriptionPayment->subscription->plan->name,
            'type' => TransactionType::PLAN_PAYMENT->value,
            'direction' => TransactionDirection::OUT->value,
        ]);
    }
}
