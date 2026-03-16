<?php

namespace App\Jobs\Source;

use App\Enums\Apify\JobStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Source\Run;
use Illuminate\Support\Facades\Log;
use App\Models\Apify\Job;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ProfileParserInterface;
use Illuminate\Support\Facades\DB;
use App\Services\Source\PostHydratorService;
use App\Services\Source\PostStatisticsService;
use App\Services\Source\TrendEventService;
use App\Services\Apify\SearchPostParserInterface;
use App\Services\Apify\SearchParserInterface;

class PostProcess implements ShouldQueue
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
        PostHydratorService $postHydratorService,
        PostStatisticsService $postStatisticsService,
        TrendEventService $trendEventService,
    ): void
    {
        Log::info('Post process: ' . $this->run->id);

        if ($this->run->source->source_type === 'manual')
        {
            $this->processManual($actorFabric, $postHydratorService, $postStatisticsService);
        }
        elseif ($this->run->source->source_type === 'search-hashtag')
        {
            $this->processSearchHashtag($actorFabric, $postHydratorService, $postStatisticsService, $trendEventService);
        }
    }

    private function processSearchHashtag(
        ActorFabric $actorFabric,
        PostHydratorService $postHydratorService,
        PostStatisticsService $postStatisticsService,
        TrendEventService $trendEventService,
    ): void
    {
         /** @var Job $searchJob */
         $searchJob = $this->run->postJob;
 
         if ($searchJob->job_status === JobStatus::SUCCEEDED->value) {
            try {
                DB::transaction(function () use ($actorFabric, $searchJob, $postHydratorService, $postStatisticsService, $trendEventService) {
                    $searchActor = $actorFabric->createActorByActorId($searchJob->actor);
                    $searchItems = $searchActor->parseDatasetItems($searchJob->job_result);
                    $count = 0;

                    /** @var SearchParserInterface $searchItem */
                    foreach ($searchItems as $searchItem) {
                        /** @var SearchPostParserInterface $postItem */
                        foreach ($searchItem->posts() as $postItem) {
                            $post = $postHydratorService->hydrate(
                                $this->run,
                                $postItem,
                                $postStatisticsService->empty(),
                                0,
                                'https://www.instagram.com/' . $postItem->ownerUsername(),
                            );

                            if (! $trendEventService->shouldPersistSearchHashtagPost($post)) {
                                continue;
                            }

                            $post->save();
                            $count++;
                            $trendEventService->createForViralPostIfNeeded($this->run, $post);
                        }
                    }

                    $this->run->update([
                        'is_post_processed' => true,
                    ]);

                    Log::info('Post process completed: ' . $this->run->id, ['count' => $count]);
                });
            } catch (\Exception $e) {
                Log::error('Post process failed: ' . $this->run->id);
                throw $e;
            }
         }

    }

    private function processManual(
        ActorFabric $actorFabric,
        PostHydratorService $postHydratorService,
        PostStatisticsService $postStatisticsService,
    ): void
    {
         /** @var Job $profileJob */
         $profileJob = $this->run->profileJob;
         /** @var Job $postJob */
         $postJob = $this->run->postJob;
 
         if ($profileJob->job_status === JobStatus::SUCCEEDED->value && $postJob->job_status === JobStatus::SUCCEEDED->value) {
             try {
                 DB::transaction(function () use ($actorFabric, $profileJob, $postJob, $postHydratorService, $postStatisticsService) {
                     $profileActor = $actorFabric->createActorByActorId($profileJob->actor);
                     $profileItems = $profileActor->parseDatasetItems($profileJob->job_result);
                     /** @var ProfileParserInterface $profileItem */
                     $profileItem = $profileItems[0];

                     $postActor = $actorFabric->createActorByActorId($postJob->actor);
                     $postItems = $postActor->parseDatasetItems($postJob->job_result);
                     $stats = $postStatisticsService->fromItems($postItems);

                     foreach ($postItems as $postItem) {
                         $post = $postHydratorService->hydrate(
                             $this->run,
                             $postItem,
                             $stats,
                             $profileItem->followersCount(),
                             $profileItem->url(),
                         );

                         $post->save();
                     }

                     $this->run->update([
                         'is_post_processed' => true,
                     ]);

                     Log::info('Post process completed: ' . $this->run->id);
                 });
             } catch (\Exception $e) {
                 Log::error('Post process failed: ' . $this->run->id);
                 throw $e;
             }
         }
    }
}
