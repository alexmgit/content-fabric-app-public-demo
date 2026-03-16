<?php

namespace App\Jobs\Source;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Source\Source;
use Illuminate\Support\Facades\Log;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ApifyJobPersister;
use App\Services\Source\SourceRunStarter;


class SourceProcess implements ShouldQueue, ShouldBeUnique
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
        public Source $source,
    ) {
        //
    }

    public function uniqueId(): string
    {
        return $this->source->id;
    }

    /**
     * Execute the job.
     */
    public function handle(
        ActorFabric $actorFabric,
        ApifyJobPersister $jobPersister,
        SourceRunStarter $sourceRunStarter,
    ): void
    {
        Log::info('Processing source', [
            'id' => $this->source->id, 
            'url' => $this->source->url, 
            'type' => $this->source->type,
            'source_type' => $this->source->source_type,
        ]);

        $run = $sourceRunStarter->start($this->source, $actorFabric, $jobPersister);
        Log::info('Run', ['run' => $run]);
    }
}
