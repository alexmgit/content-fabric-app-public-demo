<?php

namespace App\Services\Apify\Actors\GrowMediaYoutubeSearchApi;

use App\Services\Apify\SearchParserInterface;

class DatasetItem implements SearchParserInterface
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function url(): string
    {
        return $this->data['channelUrl'] ?? '';
    }

    public function ownerUsername(): string
    {
        return $this->data['channelUrl'] ?? '';
    }

    public function posts(): array
    {
        return [
            new DatasetItemPost($this->data),
        ];
    }
}