<?php

namespace App\Services\Apify;

use App\Enums\Apify\JobStatus;
use App\Models\Apify\Job;
use Illuminate\Support\Facades\Log;

class ApifyJobSyncService
{
    public function __construct(
        private readonly JobPriceCalculator $jobPriceCalculator,
    ) {
    }

    public function sync(Job $job, Client $client): void
    {
        $run = $client->getRun($job->job_id);
        $status = $run['data']['status'] ?? null;

        if ($status === JobStatus::FAILED->value) {
            Log::error('Apify API error: ', ['result' => $run]);

            $job->update([
                'job_data' => json_encode($run['data']),
                'job_status' => $status,
                'job_error' => $run['data']['errorMessage'] ?? 'Unknown error',
            ]);

            return;
        }

        if ($status !== JobStatus::SUCCEEDED->value) {
            return;
        }

        $datasetId = $run['data']['defaultDatasetId'] ?? null;

        Log::info('Getting dataset items: ', ['datasetId' => $datasetId]);

        $result = $client->getDatasetItems($datasetId);

        $job->update([
            'job_data' => json_encode($run['data']),
            'job_status' => $status,
            'job_result' => json_encode($result),
        ]);

        $job->update([
            'price' => $this->jobPriceCalculator->getPrice($job->fresh()),
        ]);
    }
}
