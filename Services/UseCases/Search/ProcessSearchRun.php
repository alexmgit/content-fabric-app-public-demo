<?php

namespace App\Services\UseCases\Search;

use App\Contracts\Logger;
use App\Enums\Apify\JobStatus;
use App\Enums\Run\RunStatus;
use App\Enums\Search\SourceInterestLevel;
use App\Models\Search\Run;
use App\Models\Search\Source;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\SearchParserInterface;
use App\Services\Apify\SearchPostParserInterface;
use App\Services\SourceInterestDetector;

class ProcessSearchRun
{
    public function __construct(private Logger $logger)
    {
    }

    public function handle(Run $run, ActorFabric $actorFabric): void
    {
        $this->logger->info('Run process: ' . $run->id);

        $searchJob = $run->searchJob;
        $sourceJob = $run->sourceJob;

        if ($sourceJob === null) {
            $this->processWithoutSourceJob($run, $actorFabric, $searchJob);
            return;
        }

        $this->processLegacy($run, $searchJob, $sourceJob);
    }

    private function processWithoutSourceJob(Run $run, ActorFabric $actorFabric, $searchJob): void
    {
        if ($searchJob->job_status !== JobStatus::SUCCEEDED->value) {
            return;
        }

        $searchActor = $actorFabric->createActorByActorId($searchJob->actor);
        $searchItems = $searchActor->parseDatasetItems($searchJob->job_result);

        $accounts = [];
        /** @var SearchParserInterface $searchItem */
        foreach ($searchItems as $searchItem) {
            /** @var SearchPostParserInterface $post */
            foreach ($searchItem->posts() as $post) {
                if (!isset($accounts[$post->ownerUsername()])) {
                    $accounts[$post->ownerUsername()] = [
                        'likes' => 0,
                        'comments' => 0,
                        'views' => 0,
                    ];
                }
                $accounts[$post->ownerUsername()]['likes'] += $post->likesCount();
                $accounts[$post->ownerUsername()]['comments'] += $post->commentsCount();
                $accounts[$post->ownerUsername()]['views'] += $post->viewsCount();
            }
        }

        $sourceInterestDetails = new SourceInterestDetector();

        foreach ($accounts as $account => $data) {
            $hash = md5($account);

            $source = Source::where('hash', $hash)->where('user_id', $run->user_id)->first();
            if ($source === null) {
                $source = new Source();
                $source->search_id = $run->search_id;
                $source->run_id = $run->id;
                $source->user_id = $run->user_id;
                $source->team_id = $run->team_id;
                $source->source_type = $run->search->search_type;
                $source->source_url = \App\Enums\Source\SourceTypes::makeUrl($run->search->search_type, $account);
                $source->hash = $hash;
            }

            $source->source_follows_count = 0;
            $source->source_followers_count = 0;
            $source->source_posts_count = 0;
            $source->search_views_count = $data['views'];
            $source->search_likes_count = $data['likes'];
            $source->search_comments_count = $data['comments'];
            $source->interest_level = $sourceInterestDetails->detect($source);

            if ($source->interest_level !== SourceInterestLevel::LOW) {
                $source->save();
            }
        }   

        $this->updateRunStatus($run, RunStatus::COMPLETED->value, 'Run process completed');
    }

    private function processLegacy(Run $run, $searchJob, $sourceJob): void
    {
        if ($searchJob->job_status === JobStatus::SUCCEEDED->value && $sourceJob->job_status === JobStatus::SUCCEEDED->value) {
            $this->updateRunStatus($run, RunStatus::COMPLETED->value, 'Run process completed');
        } else if ($searchJob->job_status === JobStatus::FAILED->value || $sourceJob->job_status === JobStatus::FAILED->value) {
            $this->updateRunStatus($run, RunStatus::FAILED->value, 'Run process failed', true);
        }
    }

    private function updateRunStatus(Run $run, string $status, string $message, bool $isError = false): void
    {
        $run->status = $status;
        $run->save();

        if ($isError) {
            $this->logger->error($message . ': ' . $run->id);
            return;
        }

        $this->logger->info($message . ': ' . $run->id);
    }
}
