<?php

namespace App\Services\Apify;

use App\Enums\Apify\JobStatus;
use App\Models\Apify\Job;

class ApifyJobPersister
{
    public function createFromRunResult(object $runResult, string $actorId, int $userId, int $teamId): Job
    {
        return Job::create([
            'actor' => $actorId,
            'job_id' => $runResult->getRunId(),
            'job_options' => json_encode($runResult->getOptions()),
            'job_data' => json_encode($runResult->getData()),
            'job_status' => $runResult->getData()['status'] ?? JobStatus::CREATED->value,
            'job_error' => $runResult->getData()['statusMessage'] ?? '',
            'user_id' => $userId,
            'team_id' => $teamId,
        ]);
    }
}
