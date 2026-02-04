<?php

namespace App\Services\Apify\Actors\ClockworksFreeTiktokScraper;

use App\Services\Apify\ActorInterface;
use App\Services\Apify\Client;
use App\Services\Apify\RunActorResult;
use Illuminate\Support\Facades\Log;

class Actor implements ActorInterface
{
    const ACTOR_ID = 'clockworks~free-tiktok-scraper';

    public function __construct(private Client $client)
    {
    }

    public function getActorId(): string
    {
        return self::ACTOR_ID;
    }

    public function run(array $options = []): RunActorResult
    {
        $options = [
            'profiles' => $options['username'],
            'resultsresultsPerPageLimit' => $options['limit'],
        ];

        Log::info('Running actor: ' . self::ACTOR_ID, ['options' => $options]);

        $result = $this->client->runActor(self::ACTOR_ID, $options);

        Log::info('Actor result: ' . self::ACTOR_ID, ['result' => $result]);

        if (!isset($result['data'], $result['data']['id'])) {
            Log::error('Apify API error: ', ['result' => $result]);

            throw new \Exception('Apify API error: ' . $result['data']['errorMessage'] ?? 'Unknown error');
        }

        return new RunActorResult($result, $options);
    }

    public function parseDatasetItems(array $items): array
    {
        return $items;
    }
}