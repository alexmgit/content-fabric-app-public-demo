<?php

namespace App\Jobs\Search;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ApifyJobPersister;
use App\Models\Search\Search;
use App\Services\Search\SearchRunStarter;

class SearchProcess implements ShouldQueue, ShouldBeUnique
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
    public function __construct(
        public Search $search,
    ) {
        //
    }

    public function uniqueId(): string
    {
        return $this->search->id;
    }

    /**
     * Execute the job.
     */
    public function handle(
        ActorFabric $actorFabric,
        ApifyJobPersister $jobPersister,
        SearchRunStarter $searchRunStarter,
    ): void
    {
        Log::info('Processing search', ['id' => $this->search->id, 'query' => $this->search->query, 'type' => $this->search->search_type]);

        $run = $searchRunStarter->start($this->search, $actorFabric, $jobPersister);
        Log::info('Run', ['run' => $run]);
    }
}
