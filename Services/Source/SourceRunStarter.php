<?php

namespace App\Services\Source;

use App\Enums\Source\RunStatus;
use App\Models\Source\Run;
use App\Models\Source\Source;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ApifyJobPersister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class SourceRunStarter
{
    public function start(Source $source, ActorFabric $actorFabric, ApifyJobPersister $jobPersister): Run
    {
        return match ($source->source_type) {
            'manual' => $this->startManual($source, $actorFabric, $jobPersister),
            'search-hashtag' => $this->startSearchHashtag($source, $actorFabric, $jobPersister),
            default => throw new \InvalidArgumentException('Unsupported source type: ' . $source->source_type),
        };
    }

    private function startSearchHashtag(Source $source, ActorFabric $actorFabric, ApifyJobPersister $jobPersister): Run
    {
        $actorSearch = $actorFabric->createActor([$source->type, 'search']);
        $runSearch = $actorSearch->run([
            'query' => $source->url,
            'limit' => $this->resolveLimit($source),
            'type' => 'hashtag',
        ]);

        return DB::transaction(function () use ($source, $actorSearch, $runSearch, $jobPersister) {
            $jobSearch = $jobPersister->createFromRunResult(
                $runSearch,
                $actorSearch->getActorId(),
                $source->user_id,
                $source->team_id,
            );

            $run = Run::create([
                'source_id' => $source->id,
                'status' => RunStatus::WAITING->value,
                'user_id' => $source->user_id,
                'team_id' => $source->team_id,
                'profile_job_id' => null,
                'post_job_id' => $jobSearch->id,
            ]);

            $source->update([
                'last_parsed_at' => Date::now(),
            ]);

            return $run;
        });
    }

    private function startManual(Source $source, ActorFabric $actorFabric, ApifyJobPersister $jobPersister): Run
    {
        $actorProfile = $actorFabric->createActor([$source->type, 'profile']);
        $actorPosts = $actorFabric->createActor([$source->type, 'posts']);

        $runProfile = $actorProfile->run([
            'username' => $source->url,
        ]);

        $runPosts = $actorPosts->run([
            'username' => $source->url,
            'limit' => $this->resolveLimit($source),
        ]);

        return DB::transaction(function () use ($source, $actorProfile, $runProfile, $actorPosts, $runPosts, $jobPersister) {
            $jobProfile = $jobPersister->createFromRunResult(
                $runProfile,
                $actorProfile->getActorId(),
                $source->user_id,
                $source->team_id,
            );

            $jobPosts = $jobPersister->createFromRunResult(
                $runPosts,
                $actorPosts->getActorId(),
                $source->user_id,
                $source->team_id,
            );

            $run = Run::create([
                'source_id' => $source->id,
                'status' => RunStatus::WAITING->value,
                'user_id' => $source->user_id,
                'team_id' => $source->team_id,
                'profile_job_id' => $jobProfile->id,
                'post_job_id' => $jobPosts->id,
            ]);

            $source->update([
                'last_parsed_at' => Date::now(),
            ]);

            return $run;
        });
    }

    private function resolveLimit(Source $source): int
    {
        if (config('app.is_use_plans')) {
            return 20;
        }

        return (int) $source->post_parse_count;
    }
}
