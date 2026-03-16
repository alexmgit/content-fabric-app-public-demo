<?php

namespace App\Jobs\Billing;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Billing\SubscriptionPayment;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\PaymentProcessingService;
use App\Services\Plan\SubscriptionRenewalService;
use YooKassa\Client as YooKassaClient;

class SubscriptionPaymentProcess implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;
 
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 10;

    /**
     * The backoff for the job.
     *
     * @var array
     */
    public $backoff = [10, 30, 60, 120, 240, 300, 300, 300, 300, 300];

     /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public SubscriptionPayment $subscriptionPayment)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->subscriptionPayment->id;
    }

    /**
     * Execute the job.
     */
    public function handle(
        YooKassaClient $yooKassaClient,
        SubscriptionRenewalService $subscriptionRenewalService,
        PaymentProcessingService $paymentProcessingService,
    ): void
    {
        try {
            $subscriptionRenewalService->process(
                $this->subscriptionPayment->fresh(),
                $yooKassaClient,
                $paymentProcessingService,
            );
        } catch (\Throwable $e) {

            $this->subscriptionPayment->update([
                'retry_count' => $this->subscriptionPayment->retry_count + 1,
            ]);

            Log::error('Subscription pay failed', [
                'user_id' => $this->subscriptionPayment->subscription->subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
