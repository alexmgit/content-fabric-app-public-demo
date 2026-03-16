<?php

namespace App\Services\Apify\Actors\ApifyInstagramScraper;

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
        return $this->data['url'] ?? '';
    }

    public function followsCount(): int
    {
        return $this->data['followsCount'] ?? 0;
    }

    public function followersCount(): int
    {
        return $this->data['followersCount'] ?? 0;
    }

    public function postsCount(): int
    {
        return $this->data['postsCount'] ?? 0;
    }
}