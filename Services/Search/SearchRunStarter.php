<?php

namespace App\Services\Search;

use App\Enums\Source\RunStatus;
use App\Models\Search\Run;
use App\Models\Search\Search;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ApifyJobPersister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class SearchRunStarter
{
    public function start(Search $search, ActorFabric $actorFabric, ApifyJobPersister $jobPersister): Run
    {
        $actorSearch = $actorFabric->createActor([$search->search_type, 'search']);
        $runSearch = $actorSearch->run([
            'query' => $search->query,
            'limit' => $this->resolveLimit($search),
            'type' => $search->query_type,
        ]);

        return DB::transaction(function () use ($search, $actorSearch, $runSearch, $jobPersister) {
            $jobSearch = $jobPersister->createFromRunResult(
                $runSearch,
                $actorSearch->getActorId(),
                $search->user_id,
                $search->team_id,
            );

            $run = Run::create([
                'search_id' => $search->id,
                'status' => RunStatus::WAITING->value,
                'user_id' => $search->user_id,
                'team_id' => $search->team_id,
                'search_job_id' => $jobSearch->id,
                'source_job_id' => null,
            ]);

            $search->update([
                'last_parsed_at' => Date::now(),
            ]);

            return $run;
        });
    }

    private function resolveLimit(Search $search): int
    {
        if (config('app.is_use_plans')) {
            return 20;
        }

        return (int) $search->parse_count;
    }
}
