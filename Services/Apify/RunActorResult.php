<?php

namespace App\Services\Apify;

class RunActorResult
{
    public function __construct(private array $result, private array $options)
    {
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getData(): array
    {
        return $this->result['data'] ?? [];
    }

    public function getRunId(): string
    {
        return $this->result['data']['id'] ?? '';
    }

    public function getRunStatus(): string
    {
        return $this->result['data']['status'] ?? '';
    }

    public function getRunError(): string
    {
        return $this->result['data']['errorMessage'] ?? '';
    }
}