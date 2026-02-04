<?php

namespace App\Services\Apify\Actors\StreamersYoutubeScraper;

use App\Services\Apify\PostParserInterface;
use Carbon\Carbon;

class DatasetItem implements PostParserInterface
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

    public function type(): string
    {
        return $this->data['type'] ?? '';
    }

    public function location(): string
    {
        return $this->data['locationName'] ?? '';
    }

    public function caption(): string
    {
        return $this->data['title'] ?? '';
    }

    public function createdAt(): Carbon
    {
        return Carbon::parse($this->data['date'] ?? 'now');
    }

    public function hashtags(): array
    {
        return $this->data['hashtags'] ?? [];
    }

    public function commentsCount(): int
    {
        return $this->data['commentsCount'] ?? 0;
    }

    public function likesCount(): int
    {
        return $this->data['likes'] ?? 0;
    }

    public function viewsCount(): int
    {
        if (isset($this->data['viewCount'])) { 
            return (int) $this->data['viewCount'];
        }

        return 0;
    }

    public function sharesCount(): int
    {
        return $this->data['sharesCount'] ?? 0;
    }

    public function videoFileUrl(): string
    {
        return '';
    }
}