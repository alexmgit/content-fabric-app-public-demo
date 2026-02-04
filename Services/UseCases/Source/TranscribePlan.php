<?php

namespace App\Services\UseCases\Source;

use App\Models\Apify\Job;

class TranscribePlan
{
    public function __construct(
        public ?Job $jobToProcess = null,
        public ?int $jobProcessDelay = null,
        public bool $retrySelf = false,
        public ?int $retryDelay = null,
    ) {}

    public static function dispatchJob(Job $job, int $jobProcessDelay, int $retryDelay): self
    {
        return new self($job, $jobProcessDelay, true, $retryDelay);
    }

    public static function retry(int $retryDelay): self
    {
        return new self(null, null, true, $retryDelay);
    }

    public static function done(): self
    {
        return new self();
    }
}
