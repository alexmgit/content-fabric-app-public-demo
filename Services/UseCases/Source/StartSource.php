<?php

namespace App\Services\UseCases\Source;

use App\Contracts\Clock;
use App\Contracts\FeatureFlags;
use App\Contracts\Logger;
use App\Contracts\TransactionManager;
use App\Enums\Apify\JobStatus;
use App\Enums\Run\RunStatus;
use App\Enums\Source\SourceInputType;
use App\Models\Apify\Job;
use App\Models\Source\Run;
use App\Models\Source\Source;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ActorInterface;

class StartSource
{
    public function __construct(
        private Logger $logger,
        private TransactionManager $tx,
        private Clock $clock,
        private FeatureFlags $features,
    ) {}

    public function handle(Source $source, ActorFabric $actorFabric): void
    {
        $this->logger->info('Processing source', [
            'id' => $source->id, 
            'url' => $source->url, 
            'type' => $source->type,
            'source_type' => $source->source_type,
        ]);

        if ($source->source_type === SourceInputType::MANUAL->value) {
            $this->processManual($source, $actorFabric);
        } elseif ($source->source_type === SourceInputType::SEARCH_HASHTAG->value) {
            $this->processSearchHashtag($source, $actorFabric);
        }
    }

    private function processSearchHashtag(Source $source, ActorFabric $actorFabric): void
    {
        $actorSearch = $actorFabric->createActor([$source->type, 'search']);

        $limit = $this->resolveLimit($source->post_parse_count);
        $runSearch = $actorSearch->run([
            'query' => $source->url,
            'limit' => $limit,
            'type' => 'hashtag',
        ]);

        $this->tx->begin();

        try {
            $jobSearch = $this->createJob($source, $actorSearch, $runSearch);
            $run = $this->createRun($source, null, $jobSearch->id);

            $source->update([
                'last_parsed_at' => $this->clock->now(),
            ]);

            $this->tx->commit();
        } catch (\Exception $e) {
            $this->tx->rollBack();
            throw $e;
        }

        $this->logger->info('Job search', ['job' => $jobSearch]);
        $this->logger->info('Run', ['run' => $run]);
    }

    private function processManual(Source $source, ActorFabric $actorFabric): void
    {
        $actorProfile = $actorFabric->createActor([$source->type, 'profile']);
        $actorPosts = $actorFabric->createActor([$source->type, 'posts']);

        $runProfile = $actorProfile->run([
            'username' => $source->url,
        ]);

        $limit = $this->resolveLimit($source->post_parse_count);
        $runPosts = $actorPosts->run([
            'username' => $source->url,
            'limit' => $limit,
        ]);

        $this->tx->begin();

        try {
            $jobProfile = $this->createJob($source, $actorProfile, $runProfile);
            $jobPosts = $this->createJob($source, $actorPosts, $runPosts);
            $run = $this->createRun($source, $jobProfile->id, $jobPosts->id);

            $source->update([
                'last_parsed_at' => $this->clock->now(),
            ]);

            $this->tx->commit();
        } catch (\Exception $e) {
            $this->tx->rollBack();
            throw $e;
        }

        $this->logger->info('Job profile', ['job' => $jobProfile]);
        $this->logger->info('Job posts', ['job' => $jobPosts]);
        $this->logger->info('Run', ['run' => $run]);
    }

    private function resolveLimit(int $fallback): int
    {
        return $this->features->usePlans() ? 20 : $fallback;
    }

    private function createJob(Source $source, ActorInterface $actor, $run): Job
    {
        return Job::create([
            'actor' => $actor->getActorId(),
            'job_id' => $run->getRunId(),
            'job_options' => json_encode($run->getOptions()),
            'job_data' => json_encode($run->getData()),
            'job_status' => $run->getData()['status'] ?? JobStatus::CREATED->value,
            'job_error' => $run->getData()['statusMessage'] ?? '',
            'user_id' => $source->user_id,
            'team_id' => $source->team_id,
        ]);
    }

    private function createRun(Source $source, ?int $profileJobId, ?int $postJobId): Run
    {
        return Run::create([
            'source_id' => $source->id,
            'status' => RunStatus::WAITING->value,
            'user_id' => $source->user_id,
            'team_id' => $source->team_id,
            'profile_job_id' => $profileJobId,
            'post_job_id' => $postJobId,
        ]);
    }
}
