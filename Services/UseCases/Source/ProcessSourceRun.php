<?php

namespace App\Services\UseCases\Source;

use App\Contracts\Logger;
use App\Enums\Apify\JobStatus;
use App\Enums\Run\RunStatus;
use App\Enums\Source\SourceInputType;
use App\Models\Source\Run;

class ProcessSourceRun
{
    public function __construct(private Logger $logger)
    {
    }

    public function handle(Run $run): void
    {
        $this->logger->info('Run process: ' . $run->id);

        if ($run->source->source_type === SourceInputType::MANUAL->value) {
            $this->processManual($run);
        } elseif ($run->source->source_type === SourceInputType::SEARCH_HASHTAG->value) {
            $this->processSearchHashtag($run);
        }
    }

    private function processSearchHashtag(Run $run): void
    {
        $searchJob = $run->postJob;

        if ($searchJob->job_status === JobStatus::SUCCEEDED->value) {
            $this->updateRunStatus($run, RunStatus::COMPLETED->value, 'Run process completed');
        } else if ($searchJob->job_status === JobStatus::FAILED->value) {
            $this->updateRunStatus($run, RunStatus::FAILED->value, 'Run process failed', true);
        }
    }

    private function processManual(Run $run): void
    {
        $profileJob = $run->profileJob;
        $postJob = $run->postJob;

        if ($profileJob->job_status === JobStatus::SUCCEEDED->value && $postJob->job_status === JobStatus::SUCCEEDED->value) {
            $this->updateRunStatus($run, RunStatus::COMPLETED->value, 'Run process completed');
        } else if ($profileJob->job_status === JobStatus::FAILED->value || $postJob->job_status === JobStatus::FAILED->value) {
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
