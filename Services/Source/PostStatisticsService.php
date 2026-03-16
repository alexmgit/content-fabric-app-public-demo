<?php

namespace App\Services\Source;

class PostStatisticsService
{
    public function empty(): array
    {
        return [
            'median_likes' => 0,
            'median_comments' => 0,
            'median_views' => 0,
            'median_shared' => 0,
            'avg_likes' => 0,
            'avg_comments' => 0,
            'avg_views' => 0,
            'avg_shared' => 0,
        ];
    }

    public function fromItems(iterable $postItems): array
    {
        $likes = [];
        $comments = [];
        $views = [];
        $shared = [];

        $avgLikes = 0;
        $avgComments = 0;
        $avgViews = 0;
        $avgShared = 0;
        $count = 0;

        foreach ($postItems as $postItem) {
            $likes[] = $postItem->likesCount();
            $comments[] = $postItem->commentsCount();
            $views[] = $postItem->viewsCount();
            $shared[] = $postItem->sharesCount();

            $avgLikes += $postItem->likesCount();
            $avgComments += $postItem->commentsCount();
            $avgViews += $postItem->viewsCount();
            $avgShared += $postItem->sharesCount();
            $count++;
        }

        if ($count === 0) {
            return $this->empty();
        }

        sort($likes);
        sort($comments);
        sort($views);
        sort($shared);

        return [
            'median_likes' => count($likes) > 2 ? $likes[(int) round(count($likes) / 2)] : 0,
            'median_comments' => count($comments) > 2 ? $comments[(int) round(count($comments) / 2)] : 0,
            'median_views' => count($views) > 2 ? $views[(int) round(count($views) / 2)] : 0,
            'median_shared' => count($shared) > 2 ? $shared[(int) round(count($shared) / 2)] : 0,
            'avg_likes' => round($avgLikes / $count),
            'avg_comments' => round($avgComments / $count),
            'avg_views' => round($avgViews / $count),
            'avg_shared' => round($avgShared / $count),
        ];
    }
}
