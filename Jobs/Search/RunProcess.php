<?php

namespace App\Jobs\Search;

use App\Enums\Apify\JobStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Search\Run;
use Illuminate\Support\Facades\Log;
use App\Models\Apify\Job;
use App\Services\Apify\ActorFabric;
use App\Services\Search\SearchSourceSyncService;
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
    public function handle(
        ActorFabric $actorFabric,
        SearchSourceSyncService $searchSourceSyncService,
        RunStatusService $runStatusService,
    ): void
    {
        Log::info('Run process: ' . $this->run->id);

        /** @var Job $searchJob */
        $searchJob = $this->run->searchJob;
        $sourceJob = $this->run->sourceJob;

        if ($sourceJob === null)
        {
            if ($searchJob->job_status === JobStatus::SUCCEEDED->value)
            {
                $searchSourceSyncService->syncFromSucceededSearchJob($this->run, $actorFabric);
                $runStatusService->markCompleted($this->run);
            }
        }
        else //LEGACY ??
        {
            if ($searchJob->job_status === JobStatus::SUCCEEDED->value && $sourceJob->job_status === JobStatus::SUCCEEDED->value) {
                $runStatusService->markCompleted($this->run);
            }
            else if ($searchJob->job_status === JobStatus::FAILED->value || $sourceJob->job_status === JobStatus::FAILED->value) {
                $runStatusService->markFailed($this->run);
            }
        }
    }
}
