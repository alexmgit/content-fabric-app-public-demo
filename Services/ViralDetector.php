<?php

namespace App\Services;

use App\Enums\Source\ViralLevel;
use App\Models\Source\Post;
use App\Enums\Source\SourceTypes;

class ViralDetector
{
    private const INSTAGRAM_THRESHOLDS = [
        ViralLevel::VIRAL->value => 100,
        ViralLevel::HIGH->value => 50,
        ViralLevel::MEDIUM->value => 20,
    ];

    private const YOUTUBE_THRESHOLDS = [
        ViralLevel::VIRAL->value => 200,
        ViralLevel::HIGH->value => 100,
        ViralLevel::MEDIUM->value => 50,
    ];

    public function detect(Post $post): ViralLevel
    {
        $viewsPerFollowers = $post->profile_followers_count > 0 ? $post->post_views_count * 100 / $post->profile_followers_count : 0;

        if ($post->source->type === SourceTypes::INSTAGRAM->value) {
            return $this->resolveByThresholds($viewsPerFollowers, self::INSTAGRAM_THRESHOLDS);
        }

        if ($post->source->type === SourceTypes::YOUTUBE->value) {
            $viewsPerMedian = $post->post_views_count_median > 0 ? $post->post_views_count * 100 / $post->post_views_count_median : 0;
            return $this->resolveByThresholds($viewsPerMedian, self::YOUTUBE_THRESHOLDS);
        }

        return ViralLevel::LOW;
    }

    private function resolveByThresholds(float|int $value, array $thresholds): ViralLevel
    {
        foreach ($thresholds as $level => $threshold) {
            if ($value > $threshold) {
                return ViralLevel::from($level);
            }
        }

        return ViralLevel::LOW;
    }
}
