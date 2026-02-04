<?php

namespace App\Services\Apify\Actors\GrowMediaYoutubeSearchApi;

use App\Services\Apify\ActorInterface;
use App\Services\Apify\Client;
use App\Services\Apify\RunActorResult;
use Illuminate\Support\Facades\Log;
use App\Services\Apify\AbstractActor;

class Actor extends AbstractActor
{
    const ACTOR_ID = 'grow_media~youtube-search-api';

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
            "maxResults" => $options['limit'],
            "order" => "date",
            "q" => $options['query'],
            "useFilters" => true,
            "videoDuration" => "short", //$options['type']
            "channelType" => "any",
            "safeSearch" => "moderate",
            "videoDefinition" => "any",
            "videoLicense" => "any"
        ];
    }
}