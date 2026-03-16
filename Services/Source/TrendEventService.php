<?php

namespace App\Services\Source;

use App\Enums\Source\ViralLevel;
use App\Models\Source\Event;
use App\Models\Source\Post;
use App\Models\Source\Run;

class TrendEventService
{
    public function shouldPersistSearchHashtagPost(Post $post): bool
    {
        if ($post->post_views_count < 10000) {
            return false;
        }
        if ($post->metric_engagement_rate < 2) {
            return false;
        }
        if ($post->metric_likes_views_ratio < 2) {
            return false;
        }
        if ($post->metric_quality_score < 2) {
            return false;
        }

        return $post->post_created_at >= now()->subMonths(3);
    }

    public function createForViralPostIfNeeded(Run $run, Post $post): void
    {
        if (! $this->isViral($post)) {
            return;
        }

        $hash = md5($post->post_url);

        if (Event::query()->where('hash', $hash)->exists()) {
            return;
        }

        Event::create([
            'source_id' => $run->source_id,
            'source_run_id' => $run->id,
            'name' => 'Новая популярная запись',
            'description' => $this->buildDescription($post),
            'is_active' => true,
            'user_id' => $run->user_id,
            'team_id' => $run->team_id,
            'hash' => $hash,
        ]);
    }

    private function isViral(Post $post): bool
    {
        return in_array($post->metric_viral_level, [ViralLevel::VIRAL, ViralLevel::HIGH], true)
            || $post->post_views_count > 1000000;
    }

    private function buildDescription(Post $post): string
    {
        $likesCount = number_format($post->post_likes_count, 0, '.', ' ');
        $commentsCount = number_format($post->post_comments_count, 0, '.', ' ');
        $viewsCount = number_format($post->post_views_count, 0, '.', ' ');

        return <<<EOT
Ссылка: [{$post->post_url}]({$post->post_url})
Лайков: {$likesCount}
Комментариев: {$commentsCount}
Просмотров: {$viewsCount}
EOT;
    }
}
