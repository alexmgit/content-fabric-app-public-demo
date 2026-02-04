<?php

namespace App\Services\Apify\Actors\ApifyInstagramPostScraper;

use App\Services\Apify\ActorInterface;
use App\Services\Apify\Client;
use App\Services\Apify\RunActorResult;
use Illuminate\Support\Facades\Log;
use App\Services\Apify\AbstractActor;

class Actor extends AbstractActor
{
    const ACTOR_ID = 'apify~instagram-post-scraper';

    public function getActorId(): string
    {
        return self::ACTOR_ID;
    }

    public function getRunOptions(array $options): array
    {
        return [
            'skipPinnedPosts' => true,
            'username' => [$options['username']],
            'resultsLimit' => $options['limit'],
        ];
    }

    public function parseDatasetItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = DatasetItem::parse($item);
        }
        return $result;
    }
}