<?php

namespace App\Services\Apify\Actors\ApifyInstagramSearchScraper;

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
        return $this->data['url'] ?? '';
    }

    public function ownerUsername(): string
    {
        return $this->data['ownerUsername'] ?? '';
    }

    public function posts(): array
    {
        $result = [];
        if (isset($this->data['topPosts']))
        {
            foreach ($this->data['topPosts'] as $post)
            {
                $result[] = new DatasetItemPost($post);
            }
        }
        if (isset($this->data['latestPosts']))
        {
            foreach ($this->data['latestPosts'] as $post)
            {
                $result[] = new DatasetItemPost($post);
            }
        }
        return $result;
    }
}