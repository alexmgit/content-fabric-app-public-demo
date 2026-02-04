<?php

namespace App\Services\Apify\Actors\GentleCloudYoutubeUserChannelScraper;

use App\Services\Apify\PostParserInterface;
use Carbon\Carbon;
use App\Services\Apify\ProfileParserInterface;

class DatasetItem implements ProfileParserInterface
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function parse(array $data): self
    {
        return new self($data);
    }

    public function url(): string
    {
        return $this->data['indexUrl'] ?? '';
    }

    public function followsCount(): int
    {
        return 0;
    }

    public function followersCount(): int
    {
        return $this->data['subscriberCount'] ?? 0;
    }

    public function postsCount(): int
    {
        return $this->data['videoCount'] ?? 0;
    }
}