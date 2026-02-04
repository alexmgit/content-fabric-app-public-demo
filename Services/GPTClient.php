<?php

namespace App\Services;

use OpenAI;
use GuzzleHttp\Client;

class GPTClient
{
    protected $client;
    protected $model;
    protected $temperature;

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

    public function chat()
    {
        return $this->client->chat();
    }

    public function audio()
    {
        return $this->client->audio();
    }

    public function getModel()
    {
        return $this->model;
    }   

    public function getTemperature()
    {
        return $this->temperature;
    }
}
