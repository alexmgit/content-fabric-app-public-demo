<?php

namespace App\Services\Apify\Actors\ApifyInstagramSearchScraper;

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

    public function type(): string
    {
        return $this->data['type'] ?? '';
    }

    public function location(): string
    {
        return $this->data['locationName'] ?? '';
    }

    public function createdAt(): Carbon
    {
        return Carbon::parse($this->data['timestamp'] ?? 'now');
    }

    public function ownerUsername(): string
    {
        return $this->data['ownerUsername'] ?? '';
    }

    public function likesCount(): int
    {
        return abs($this->data['likesCount'] ?? 0);
    }

    public function commentsCount(): int
    {
        return $this->data['commentsCount'] ?? 0;
    }

    public function sharesCount(): int
    {
        return $this->data['sharesCount'] ?? 0;

        if (isset($this->data['sharesCount'])) { 
            return (int) $this->data['sharesCount'];
        }

        if (isset($this->data['reshareCount'])) {
            return (int) $this->data['reshareCount'];
        }
        return 0;
    }

    public function viewsCount(): int
    {
        if (isset($this->data['viewsCount'])) { 
            return (int) $this->data['viewsCount'];
        }

        if (isset($this->data['videoPlayCount'])) {
            return (int) $this->data['videoPlayCount'];
        }
        return 0;
    }

    public function caption(): string
    {
        return $this->data['caption'] ?? '';
    }

    public function videoFileUrl(): string
    {
        if (isset($this->data['videoUrl'])) { 
            return $this->data['videoUrl'];
        }
        return '';
    }
}