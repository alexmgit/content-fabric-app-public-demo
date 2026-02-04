<?php

namespace App\Jobs\Source;

use App\Models\Source\PostTranscribe;
use App\Services\Apify\ActorFabric;
use App\Services\UseCases\Source\TranscribePost;
use App\Services\UseCases\Source\TranscribePlan;
use App\Jobs\Apify\JobProcess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class TranscribeProcess implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;
 
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;
    
    /**
     * The backoff for the job.
     *
     * @var array
     */
    public $backoff = [10, 30, 60, 120, 240, 480, 960, 1920, 3840, 7680];
    
    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(public PostTranscribe $postTranscribe)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->postTranscribe->id;
    }

    /**
     * Execute the job.
     */
    public function handle(ActorFabric $actorFabric, TranscribePost $service): void
    {        
        $plan = $service->handle($this->postTranscribe, $actorFabric);
        $this->applyPlan($plan);
    }

    public function failed(?Throwable $exception): void
    {
        $service = app(TranscribePost::class);
        $service->failed($this->postTranscribe);
    }

    private function applyPlan(TranscribePlan $plan): void
    {
        if ($plan->jobToProcess && $plan->jobProcessDelay !== null) {
            JobProcess::dispatch($plan->jobToProcess)->delay($plan->jobProcessDelay);
        }

        if ($plan->retrySelf && $plan->retryDelay !== null) {
            self::dispatch($this->postTranscribe)->delay($plan->retryDelay);
        }
    }
}
