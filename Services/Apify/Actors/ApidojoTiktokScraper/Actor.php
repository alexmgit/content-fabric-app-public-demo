<?php

namespace App\Services\Apify\Actors\ApidojoTiktokScraper;

use App\Services\Apify\AbstractActor;

class Actor extends AbstractActor
{
    const ACTOR_ID = 'apidojo~tiktok-scraper';

    public function getActorId(): string
    {
        return self::ACTOR_ID;
    }

    public function getRunOptions(array $options): array
    {
        return [
            'startUrls' => $options['username'],
            'maxItems' => $options['limit'],
        ];
    }

    public function parseDatasetItems(array $items): array
    {
        return $items;
    }
}
