<?php

namespace App\Services\Apify\Actors\ClockworksFreeTiktokScraper;

use App\Services\Apify\AbstractActor;

class Actor extends AbstractActor
{
    const ACTOR_ID = 'clockworks~free-tiktok-scraper';

    public function getActorId(): string
    {
        return self::ACTOR_ID;
    }

    public function getRunOptions(array $options): array
    {
        return [
            'profiles' => $options['username'],
            'resultsresultsPerPageLimit' => $options['limit'],
        ];
    }

    public function parseDatasetItems(array $items): array
    {
        return $items;
    }
}
