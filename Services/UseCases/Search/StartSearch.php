<?php

namespace App\Services\UseCases\Search;

use App\Contracts\Clock;
use App\Contracts\FeatureFlags;
use App\Contracts\Logger;
use App\Contracts\TransactionManager;
use App\Enums\Apify\JobStatus;
use App\Enums\Run\RunStatus;
use App\Models\Apify\Job;
use App\Models\Search\Run;
use App\Models\Search\Search;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ActorInterface;

class StartSearch
{
    public function __construct(
        private Logger $logger,
        private TransactionManager $tx,
        private Clock $clock,
        private FeatureFlags $features,
    ) {}

    public function handle(Search $search, ActorFabric $actorFabric): void
    {
        $this->logger->info('Processing search', [
            'id' => $search->id,
            'query' => $search->query,
            'type' => $search->search_type,
        ]);

        $actorSearch = $actorFabric->createActor([$search->search_type, 'search']);
        $limit = $this->resolveLimit($search->parse_count);
        $runSearch = $actorSearch->run([
            'query' => $search->query,
            'limit' => $limit,
            'type' => $search->query_type,
        ]);

        $this->tx->begin();

        try {
            $jobSearch = $this->createJob($search, $actorSearch, $runSearch);
            $run = $this->createRun($search, $jobSearch);

            $search->update([
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

    private function resolveLimit(int $fallback): int
    {
        return $this->features->usePlans() ? 20 : $fallback;
    }

    private function createJob(Search $search, ActorInterface $actorSearch, $runSearch): Job
    {
        return Job::create([
            'actor' => $actorSearch->getActorId(),
            'job_id' => $runSearch->getRunId(),
            'job_options' => json_encode($runSearch->getOptions()),
            'job_data' => json_encode($runSearch->getData()),
            'job_status' => $runSearch->getData()['status'] ?? JobStatus::CREATED->value,
            'job_error' => $runSearch->getData()['statusMessage'] ?? '',
            'user_id' => $search->user_id,
            'team_id' => $search->team_id,
        ]);
    }

    private function createRun(Search $search, Job $jobSearch): Run
    {
        return Run::create([
            'search_id' => $search->id,
            'status' => RunStatus::WAITING->value,
            'user_id' => $search->user_id,
            'team_id' => $search->team_id,
            'search_job_id' => $jobSearch->id,
            'source_job_id' => null,
        ]);
    }
}
