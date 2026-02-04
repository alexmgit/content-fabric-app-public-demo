<?php

namespace App\Services\Apify\Actors\ApifyInstagramSearchScraper;

use App\Services\Apify\ActorInterface;
use App\Services\Apify\Client;
use App\Services\Apify\RunActorResult;
use Illuminate\Support\Facades\Log;
use App\Services\Apify\AbstractActor;

class Actor extends AbstractActor
{
    const ACTOR_ID = 'apify~instagram-search-scraper';

    public function getActorId(): string
    {
        return self::ACTOR_ID;
    }

    public function parseDatasetItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = new DatasetItem($item);
        }
        return $result;
    }

    public function getRunOptions(array $options): array
    {
        return [
            "enhanceUserSearchWithFacebookPage" => false,
            "search" => $options['query'],
            "searchLimit" => $options['limit'],
            "searchType" => $this->resolveType($options['type']), //user, hashtag, place
        ];
    }

    private function resolveType($type): string
    {
        return match ($type) {
            "user", "username" => 'user',
            "hashtag" => 'hashtag',
            "place" => 'place',
            default => 'hashtag',
        };
    }
}