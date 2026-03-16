<?php

namespace App\Services\Plan;

use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\SubscriptionPaymentStatus;
use App\Enums\Billing\TransactionDirection;
use App\Enums\Billing\TransactionType;
use App\Mail\Plan\PlanExpire;
use App\Models\Billing\PaymentMethod;
use App\Models\Billing\SubscriptionPayment;
use App\Models\Billing\Transaction;
use App\Services\Payment\PaymentManagerFactory;
use App\Services\Payment\PaymentProcessingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use YooKassa\Client;

class SubscriptionRenewalService
{
    private const MAX_TRY = 5;

    public function __construct(
        private readonly PaymentManagerFactory $paymentManagerFactory,
    ) {
    }

    public function process(
        SubscriptionPayment $subscriptionPayment,
        Client $client,
        PaymentProcessingService $paymentProcessingService,
    ): void {
        if ($subscriptionPayment->status !== SubscriptionPaymentStatus::WAITING->value) {
            return;
        }

        if ((int) $subscriptionPayment->retry_count >= self::MAX_TRY) {
            $this->failAndExpire($subscriptionPayment);
            return;
        }

        $subscriber = $subscriptionPayment->subscription->subscriber;
        $plan = $subscriptionPayment->subscription->plan;
        $moneyLeft = $subscriber->balance - $plan->price;

        if ($moneyLeft < 0) {
            $paymentMethod = PaymentMethod::query()
                ->where('user_id', $subscriber->id)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();

            if (! $paymentMethod) {
                $this->failAndExpire($subscriptionPayment);
                return;
            }

            if (! $this->tryAutoPayment($subscriptionPayment, $paymentMethod, $client, $paymentProcessingService, $moneyLeft)) {
                $subscriptionPayment->update([
                    'retry_count' => $subscriptionPayment->retry_count + 1,
                ]);

                return;
            }
        }

        DB::transaction(function () use ($subscriptionPayment, $subscriber, $plan) {
            Transaction::create([
                'user_id' => $subscriber->id,
                'team_id' => $subscriber->current_team_id,
                'amount' => -$plan->price,
                'currency' => 'RUB',
                'description' => 'Продление тарифа ' . $plan->name,
                'type' => TransactionType::PLAN_PAYMENT->value,
                'direction' => TransactionDirection::OUT->value,
            ]);

            $subscriptionPayment->update([
                'status' => SubscriptionPaymentStatus::PAID->value,
            ]);
        });
    }

    private function tryAutoPayment(
        SubscriptionPayment $subscriptionPayment,
        PaymentMethod $paymentMethod,
        Client $client,
        PaymentProcessingService $paymentProcessingService,
        float|int $moneyLeft,
    ): bool {
        $payment = $subscriptionPayment->payment;

        if ($payment === null) {
            $paymentManager = $this->paymentManagerFactory->make($client);

            $payment = $paymentManager->createPaymentWithToken(
                $subscriptionPayment->subscription->subscriber,
                ['amount' => abs($moneyLeft)],
                $paymentMethod->payment_method_id
            );

            $subscriptionPayment->update([
                'payment_id' => $payment->id,
            ]);
        }

        $payment = $payment->fresh();

        if ($payment->status === PaymentStatus::PAID->value) {
            return true;
        }

        return $paymentProcessingService->processAutoPayment($payment, $client);
    }

    private function failAndExpire(SubscriptionPayment $subscriptionPayment): void
    {
        $mailTo = $subscriptionPayment->subscription->subscriber;

        DB::transaction(function () use ($subscriptionPayment) {
            $subscriptionPayment->update([
                'status' => SubscriptionPaymentStatus::FAILED->value,
            ]);

            $subscriptionPayment->subscription->delete();
        });

        Mail::to($mailTo)->send(new PlanExpire());
    }
}
