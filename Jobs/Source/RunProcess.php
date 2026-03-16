<?php

namespace App\Jobs\Source;

use App\Enums\Apify\JobStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Source\Run;
use Illuminate\Support\Facades\Log;
use App\Models\Apify\Job;
use App\Services\Source\RunStatusService;

class RunProcess implements ShouldQueue
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
    public function __construct(public Run $run)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->run->id;
    }

    /**
     * Execute the job.
     */
    public function handle(RunStatusService $runStatusService): void
    {
        Log::info('Run process: ' . $this->run->id);

        if ($this->run->source->source_type === 'manual')
        {
            $this->processManual($runStatusService);
        }
        elseif ($this->run->source->source_type === 'search-hashtag')
        {
            $this->processSearchHashtag($runStatusService);
        }
    }

    private function processSearchHashtag(RunStatusService $runStatusService): void
    {
        /** @var Job $searchJob */
        $searchJob = $this->run->postJob;

        if ($searchJob->job_status === JobStatus::SUCCEEDED->value) {
            $runStatusService->markCompleted($this->run);
            Log::info('Run process completed: ' . $this->run->id);
        } else if ($searchJob->job_status === JobStatus::FAILED->value) {
            $runStatusService->markFailed($this->run);
            Log::error('Run process failed: ' . $this->run->id);
        }
    }

    private function processManual(RunStatusService $runStatusService): void
    {
        /** @var Job $profileJob */
        $profileJob = $this->run->profileJob;
        /** @var Job $postJob */
        $postJob = $this->run->postJob;

        if ($profileJob->job_status === JobStatus::SUCCEEDED->value && $postJob->job_status === JobStatus::SUCCEEDED->value) {
            $runStatusService->markCompleted($this->run);
            Log::info('Run process completed: ' . $this->run->id);
        } else if ($profileJob->job_status === JobStatus::FAILED->value || $postJob->job_status === JobStatus::FAILED->value) {
            $runStatusService->markFailed($this->run);
            Log::error('Run process failed: ' . $this->run->id);
        }
    }
}
