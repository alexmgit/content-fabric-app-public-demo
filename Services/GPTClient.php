<?php

namespace App\Services;

use GuzzleHttp\Client;
use OpenAI;

class GPTClient
{
    protected readonly mixed $client;
    protected readonly string $model;
    protected readonly float|int $temperature;

    public function __construct()
    {
        $this->model = config('openai.model');
        $this->temperature = config('openai.temperature');

        $httpClient = new Client(
            [
                'proxy' => config('openai.proxy'),
            ]
        );

        $this->client = OpenAI::factory()
            ->withApiKey(config('openai.api_key'))
            ->withBaseUri(config('openai.base_url'))
            ->withHttpClient($httpClient)
            ->make();
    }

    public function chat(): mixed
    {
        return $this->client->chat();
    }

    public function audio(): mixed
    {
        return $this->client->audio();
    }

    public function getModel(): string
    {
        return $this->model;
    }   

    public function getTemperature(): float|int
    {
        return $this->temperature;
    }
}
