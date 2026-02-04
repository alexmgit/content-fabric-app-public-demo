<?php

namespace App\Jobs\Search;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Apify\ActorFabric;
use App\Models\Search\Search;
use App\Services\UseCases\Search\StartSearch;

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
    public function handle(ActorFabric $actorFabric, StartSearch $service): void
    {
        $service->handle($this->search, $actorFabric);
    }
}
