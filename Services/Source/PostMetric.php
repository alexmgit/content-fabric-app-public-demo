<?php

namespace App\Services\Source;

use App\Models\Source\Post;
use Carbon\Carbon;
use App\Enums\Source\ViralLevel;

class PostMetric
{
    protected $quality_score_weight = [
        'engagement_rate' => 0.5,
        'likes_views_ratio' => 0.3,
        'comments_views_ratio' => 0.2,
    ];

    /**
     * ER (Engagement Rate)	(Лайки + Комментарии) / Просмотры × 100%
     */
    public function calculateEngagementRate(Post $post)
    {
        return $post->post_views_count > 0 ? (($post->post_likes_count + $post->post_comments_count) / $post->post_views_count) * 100 : 0;
    }

    /**
     * ERF (Engagement-to-Follower)	(Лайки + Комментарии) / Подписчики × 100%
     */
    public function calculateEngagementRateFollowers(Post $post)
    {
        return $post->profile_followers_count > 0 ? ($post->post_likes_count + $post->post_comments_count) / $post->profile_followers_count * 100 : 0;
    }

    /**
     * V/F Ratio (Просмотры/Подписчики)	Просмотры / Подписчики × 100%
     */
    public function calculateViewsFollowersRatio(Post $post)
    {
        return $post->profile_followers_count > 0 ? $post->post_views_count / $post->profile_followers_count * 100 : 0;
    }

    /**
     * LVR (Like-to-View Ratio)	Лайки / Просмотры × 100%
     */
    public function calculateLikesViewsRatio(Post $post)
    {
        return $post->post_views_count > 0 ? $post->post_likes_count / $post->post_views_count * 100 : 0;
    }

    /**
     * CVR (Comment-to-View Ratio)	Комментарии / Просмотры × 100%
     */
    public function calculateCommentsViewsRatio(Post $post)
    {
        return $post->post_views_count > 0 ? $post->post_comments_count / $post->post_views_count * 100 : 0;
    }

    /**
     * EEngagement Velocity (EV)	(Лайки + Комменты) / Время с публикации (часы)
     */
    public function calculateEngagementVelocity(Post $post)
    {
        $diff_hours = Carbon::parse($post->post_created_at)->diffInHours(now());
        return $diff_hours > 0 ? ($post->post_likes_count + $post->post_comments_count) / $diff_hours : 0;
    }

    /**
     * Quality Score (Индекс качества)	ER × 0.5 + LVR × 0.3 + CVR × 0.2
     */
    public function calculateQualityScore(Post $post)
    {
        return $this->calculateEngagementRate($post) * $this->quality_score_weight['engagement_rate'] + 
            $this->calculateLikesViewsRatio($post) * $this->quality_score_weight['likes_views_ratio'] + 
            $this->calculateCommentsViewsRatio($post) * $this->quality_score_weight['comments_views_ratio'];
    }

    /**
     * Вирусность (авто)	
     * Правило на основе V/F и LVR	Автоматическая оценка вирусности	
     * V/F>200% и LVR>10% → Вирусная;
     * V/F>100% и LVR>5% → Высокая;
     * 50–100% и 2–5% → Средняя;
     * иначе → Низкая
     */
    public function calculateViralLevel(Post $post)
    {
        if ($post->profile_followers_count > 0)
        {
            $views_followers_ratio = $this->calculateViewsFollowersRatio($post);
            $likes_views_ratio = $this->calculateLikesViewsRatio($post);
    
            if ($views_followers_ratio > 100 && $likes_views_ratio > 5) {
                return ViralLevel::VIRAL;
            } elseif ($views_followers_ratio > 50 && $likes_views_ratio > 2) {
                return ViralLevel::HIGH;
            } elseif ($views_followers_ratio > 25 && $likes_views_ratio > 1) {
                return ViralLevel::MEDIUM;
            } else {
                return ViralLevel::LOW;
            }
        }
        else
        {
            $calculateQualityScore = $this->calculateQualityScore($post);
            
            if ($calculateQualityScore > 50) {
                return ViralLevel::VIRAL;
            } elseif ($calculateQualityScore > 15) {
                return ViralLevel::HIGH;
            } elseif ($calculateQualityScore > 5) {
                return ViralLevel::MEDIUM;
            } else {
                return ViralLevel::LOW;
            }
        }
    }
    
}