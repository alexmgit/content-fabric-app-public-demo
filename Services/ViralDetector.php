<?php

namespace App\Services;

use App\Enums\Source\ViralLevel;
use App\Models\Source\Post;
use App\Enums\Source\SourceTypes;

class ViralDetector
{
    public function detect(Post $post): ViralLevel
    {
        $viewsPerFollowers = $post->profile_followers_count > 0 ? $post->post_views_count * 100 / $post->profile_followers_count : 0;

        if ($post->source->type === SourceTypes::INSTAGRAM->value) {
            if ($viewsPerFollowers > 100) {
                return ViralLevel::VIRAL;
            }
    
            if ($viewsPerFollowers > 50) {
                return ViralLevel::HIGH;
            }
    
            if ($viewsPerFollowers > 20) {
                return ViralLevel::MEDIUM;
            }
        }
        elseif ($post->source->type === SourceTypes::YOUTUBE->value) {
            $viewsPerMedian = $post->post_views_count_median > 0 ? $post->post_views_count * 100 / $post->post_views_count_median : 0;

            if ($viewsPerMedian > 200) {
                return ViralLevel::VIRAL;
            }

            if ($viewsPerMedian > 100) {
                return ViralLevel::HIGH;
            }

            if ($viewsPerMedian > 50) {
                return ViralLevel::MEDIUM;
            }
        }

        return ViralLevel::LOW;
    }
}