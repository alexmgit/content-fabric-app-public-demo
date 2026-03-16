<?php

namespace App\Services\Apify\Actors\ApidojoInstagramScraper;

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
        if (isset($this->data['isVideo']) && $this->data['isVideo']) {
            return 'video';
        }

        if (isset($this->data['isCarousel']) && $this->data['isCarousel']) {
            return 'carousel';
        }
        return 'image';
    }

    public function location(): string
    {
        return '';
    }

    public function caption(): string
    {
        return $this->data['caption'] ?? '';
    }

    public function createdAt(): Carbon
    {
        return Carbon::parse($this->data['createdAt'] ?? 'now');
    }

    public function hashtags(): array
    {
        return $this->data['hashtags'] ?? [];
    }

    public function commentsCount(): int
    {
        return $this->data['commentCount'] ?? 0;
    }

    public function likesCount(): int
    {
        return $this->data['likeCount'] ?? 0;
    }

    public function viewsCount(): int
    {
        if (isset($this->data['video']['playCount'])) { 
            return (int) $this->data['video']['playCount'];
        }

        return 0;
    }

    public function sharesCount(): int
    {
        return $this->data['sharesCount'] ?? 0;
    }

    public function videoFileUrl(): string
    {
        if (isset($this->data['video']['url'])) { 
            return $this->data['video']['url'];
        }
        return '';
    }
}