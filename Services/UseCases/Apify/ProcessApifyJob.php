<?php

namespace App\Services\UseCases\Apify;

use App\Contracts\Logger;
use App\Enums\Apify\JobStatus;
use App\Models\Apify\Job;
use App\Services\Apify\Client;
use App\Services\Apify\JobPriceCalculator;

class ProcessApifyJob
{
    public function __construct(
        private Logger $logger,
        private Client $client,
    )
    {
    }

    public function handle(Job $apifyJob): void
    {
        $run = $this->client->getRun($apifyJob->job_id);

        if ($run['data']['status'] === JobStatus::FAILED->value) {
            $this->handleFailedRun($apifyJob, $run);
            return;
        } else if ($run['data']['status'] === JobStatus::SUCCEEDED->value) {
            $this->handleSucceededRun($apifyJob, $run);
        }
    }

    private function handleFailedRun(Job $apifyJob, array $run): void
    {
        $this->logger->error('Apify API error: ', ['result' => $run]);

        $apifyJob->update([
            'job_data' => json_encode($run['data']),
            'job_status' => $run['data']['status'],
            'job_error' => $run['data']['errorMessage'] ?? 'Unknown error',
        ]);
    }

    private function handleSucceededRun(Job $apifyJob, array $run): void
    {
        $datasetId = $run['data']['defaultDatasetId'] ?? null;

        $this->logger->info('Getting dataset items: ', ['datasetId' => $datasetId]);

        $result = $this->client->getDatasetItems($datasetId);

        $apifyJob->update([
            'job_data' => json_encode($run['data']),
            'job_status' => $run['data']['status'],
            'job_result' => json_encode($result),
        ]);

        $apifyJob->update([
            'price' => (new JobPriceCalculator)->getPrice($apifyJob),
        ]);
    }
}
