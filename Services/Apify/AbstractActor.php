<?php

namespace App\Services\Apify;

use Illuminate\Support\Facades\Log;

abstract class AbstractActor implements ActorInterface
{
    abstract public function getActorId(): string;

    public function __construct(private Client $client)
    {

    }

    public function run(array $options = []): RunActorResult
    {
        $options = $this->getRunOptions($options);

        Log::info('Running actor: ' . $this->getActorId(), ['options' => $options]);

        $result = $this->client->runActor($this->getActorId(), $options);

        Log::info('Actor result: ' . $this->getActorId(), ['result' => $result]);

        if (!isset($result['data'], $result['data']['id'])) {
            Log::error('Apify API error: ', ['result' => $result]);

            throw new \Exception('Apify API error: ' . $result['data']['errorMessage'] ?? 'Unknown error');
        }

        return new RunActorResult($result, $options);
    }

    abstract public function getRunOptions(array $options): array;
}