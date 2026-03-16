<?php

namespace App\Services\Apify;

use Illuminate\Support\Facades\Log;
use RuntimeException;

abstract class AbstractActor implements ActorInterface
{
    abstract public function getActorId(): string;

    abstract public function getRunOptions(array $options): array;

    abstract public function parseDatasetItems(array $items): array;

    public function __construct(private Client $client)
    {
    }

    public function run(array $options = []): RunActorResult
    {
        $options = $this->getRunOptions($options);

        Log::info('Running actor: ' . $this->getActorId(), ['options' => $options]);

        $result = $this->client->runActor($this->getActorId(), $options);

        Log::info('Actor result: ' . $this->getActorId(), ['result' => $result]);

        $this->assertRunResult($result);

        return new RunActorResult($result, $options);
    }

    protected function assertRunResult(array $result): void
    {
        if (isset($result['data']['id'])) {
            return;
        }

        Log::error('Apify actor returned invalid run payload', [
            'actor' => $this->getActorId(),
            'result' => $result,
        ]);

        $message = $result['data']['errorMessage']
            ?? $result['error']['message']
            ?? 'Unknown error';

        throw new RuntimeException('Apify API error: ' . $message);
    }
}
