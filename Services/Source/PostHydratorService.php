<?php

namespace App\Services\Source;

use App\Models\Source\Post;
use App\Models\Source\Run;
use App\Services\ViralDetector;

class PostHydratorService
{
    public function __construct(
        private readonly ViralDetector $viralDetector,
        private readonly PostMetric $postMetric,
    ) {
    }

    public function hydrate(Run $run, object $postItem, array $stats, int $followersCount, string $profileUrl): Post
    {
        $hash = md5($postItem->url());

        $post = Post::query()
            ->where('post_hash', $hash)
            ->where('user_id', $run->user_id)
            ->first() ?? new Post([
                'source_id' => $run->source_id,
                'run_id' => $run->id,
                'user_id' => $run->user_id,
                'team_id' => $run->team_id,
                'post_hash' => $hash,
            ]);

        $post->post_url = $postItem->url();
        $post->post_created_at = $postItem->createdAt();
        $post->post_likes_count = $postItem->likesCount();
        $post->post_comments_count = $postItem->commentsCount();
        $post->post_views_count = $postItem->viewsCount();
        $post->post_shared_count = $postItem->sharesCount();
        $post->post_likes_count_median = $stats['median_likes'];
        $post->post_comments_count_median = $stats['median_comments'];
        $post->post_views_count_median = $stats['median_views'];
        $post->post_shared_count_median = $stats['median_shared'];
        $post->post_likes_count_avg = $stats['avg_likes'];
        $post->post_comments_count_avg = $stats['avg_comments'];
        $post->post_views_count_avg = $stats['avg_views'];
        $post->post_shared_count_avg = $stats['avg_shared'];
        $post->post_type = $postItem->type();
        $post->post_caption = $postItem->caption();
        $post->post_location = $postItem->location();
        $post->profile_followers_count = $followersCount;
        $post->profile_url = $profileUrl;
        $post->viral_level = $this->viralDetector->detect($post);
        $post->metric_engagement_rate = $this->postMetric->calculateEngagementRate($post);
        $post->metric_engagement_rate_followers = $this->postMetric->calculateEngagementRateFollowers($post);
        $post->metric_views_followers_ratio = $this->postMetric->calculateViewsFollowersRatio($post);
        $post->metric_likes_views_ratio = $this->postMetric->calculateLikesViewsRatio($post);
        $post->metric_comments_views_ratio = $this->postMetric->calculateCommentsViewsRatio($post);
        $post->metric_engagement_velocity = $this->postMetric->calculateEngagementVelocity($post);
        $post->metric_quality_score = $this->postMetric->calculateQualityScore($post);
        $post->metric_viral_level = $this->postMetric->calculateViralLevel($post);

        return $post;
    }
}
