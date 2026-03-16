<?php

namespace App\Services\Apify\Actors\GrowMediaYoutubeSearchApi;

use App\Services\Apify\SearchPostParserInterface;
use Carbon\Carbon;

class DatasetItemPost implements SearchPostParserInterface
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function url(): string
    {
        return $this->data['url'] ?? '';
    }

    public function ownerUsername(): string
    {
        return $this->data['channelUrl'] ?? '';
    }

    public function likesCount(): int
    {
        return $this->data['likes'] ?? 0;
    }

    public function commentsCount(): int
    {
        return $this->data['commentsCount'] ?? 0;
    }

    public function viewsCount(): int
    {
        return $this->data['viewCount'] ?? 0;
    }

    public function location(): string { return ''; }

    public function type(): string { return ''; }

    public function createdAt(): Carbon { return Carbon::parse($this->data['timestamp'] ?? 'now'); }

    public function sharesCount(): int { return ''; }

    public function caption(): string { return ''; }

    public function videoFileUrl(): string { return ''; }
}