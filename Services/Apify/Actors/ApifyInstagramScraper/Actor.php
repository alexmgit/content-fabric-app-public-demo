<?php

namespace App\Services\Apify\Actors\ApifyInstagramScraper;

use App\Services\Apify\ActorInterface;
use App\Services\Apify\Client;
use App\Services\Apify\RunActorResult;
use Illuminate\Support\Facades\Log;
use App\Services\Apify\AbstractActor;

class Actor extends AbstractActor
{
    const ACTOR_ID = 'apify~instagram-scraper';

    public function getActorId(): string
    {
        return self::ACTOR_ID;
    }

    public function parseDatasetItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = DatasetItem::parse($item);
        }
        return $result;
    }

    public function getRunOptions(array $options): array
    {
        return [
            'directUrls' => [$options['username']],
            'resultsLimit' => 1,
            'resultsType' => 'details',
        ];
    }
}