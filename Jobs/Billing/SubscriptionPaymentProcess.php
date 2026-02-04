<?php

namespace App\Jobs\Billing;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Billing\SubscriptionPayment;
use YooKassa\Client as YooKassaClient;
use App\Services\UseCases\Billing\SubscriptionPaymentHandler;

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
    public function handle(YooKassaClient $yooKassaClient, SubscriptionPaymentHandler $service): void
    {
        $service->handle($this->subscriptionPayment, $yooKassaClient);
    }
}
