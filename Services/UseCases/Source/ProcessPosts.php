<?php

namespace App\Services\UseCases\Source;

use App\Contracts\Clock;
use App\Contracts\Logger;
use App\Contracts\TransactionManager;
use App\Enums\Apify\JobStatus;
use App\Enums\Source\SourceInputType;
use App\Enums\Source\ViralLevel;
use App\Models\Source\Event;
use App\Models\Source\Post;
use App\Models\Source\Run;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ProfileParserInterface;
use App\Services\Apify\PostParserInterface;
use App\Services\Apify\SearchParserInterface;
use App\Services\Apify\SearchPostParserInterface;
use App\Services\Source\PostMetric;
use App\Services\ViralDetector;

class ProcessPosts
{
    private const MIN_VIEWS = 10000;
    private const MIN_ENGAGEMENT_RATE = 2;
    private const MIN_LIKES_VIEWS_RATIO = 2;
    private const MIN_QUALITY_SCORE = 2;
    private const MAX_AGE_MONTHS = 3;
    private const VIRAL_VIEWS = 1000000;

    public function __construct(
        private Logger $logger,
        private TransactionManager $tx,
        private Clock $clock,
    ) {}

    public function handle(Run $run, ActorFabric $actorFabric): void
    {
        $this->logger->info('Post process: ' . $run->id);

        if ($run->source->source_type === SourceInputType::MANUAL->value) {
            $this->processManual($run, $actorFabric);
        } elseif ($run->source->source_type === SourceInputType::SEARCH_HASHTAG->value) {
            $this->processSearchHashtag($run, $actorFabric);
        }
    }

    private function processSearchHashtag(Run $run, ActorFabric $actorFabric): void
    {
        $searchJob = $run->postJob;

        if ($searchJob->job_status !== JobStatus::SUCCEEDED->value) {
            return;
        }

        try {
            $this->tx->begin();

            $searchActor = $actorFabric->createActorByActorId($searchJob->actor);
            $searchItems = $searchActor->parseDatasetItems($searchJob->job_result);

            $viralDetector = new ViralDetector();
            $postMetric = new PostMetric();

            $count = 0;

            /** @var SearchParserInterface $searchItem */
            foreach ($searchItems as $searchItem) {
                /** @var SearchPostParserInterface $postItem */
                foreach ($searchItem->posts() as $postItem) {
                    $hash = md5($postItem->url());  

                    $post = Post::where('post_hash', $hash)->where('user_id', $run->user_id)->first();
                    if (!$post) {
                        $post = new Post();
                        $post->source_id = $run->source_id;
                        $post->run_id = $run->id;
                        $post->user_id = $run->user_id;
                        $post->team_id = $run->team_id;
                        $post->post_hash = $hash;
                    }

                    $post->post_url = $postItem->url();
                    $post->post_created_at = $postItem->createdAt();
                    $post->post_likes_count = $postItem->likesCount();
                    $post->post_comments_count = $postItem->commentsCount();
                    $post->post_views_count = $postItem->viewsCount();
                    $post->post_shared_count = $postItem->sharesCount();
                    $post->post_likes_count_median = 0;
                    $post->post_comments_count_median = 0;
                    $post->post_views_count_median = 0;
                    $post->post_shared_count_median = 0;
                    $post->post_likes_count_avg = 0;
                    $post->post_comments_count_avg = 0;
                    $post->post_views_count_avg = 0;
                    $post->post_shared_count_avg = 0;
                    $post->post_type = $postItem->type();
                    $post->post_caption = $postItem->caption();
                    $post->post_location = $postItem->location();
                    $post->profile_followers_count = 0;
                    $post->profile_url = 'https://www.instagram.com/' . $postItem->ownerUsername();
                    $post->viral_level = $viralDetector->detect($post);
    
                    $this->fillMetrics($postMetric, $post);

                    if ($this->shouldSkipPost($post)) {
                        continue;
                    }

                    $post->save();

                    $count++;

                    if ($this->isViral($post)) {
                        $this->createViralEvent($run, $postItem);
                    }
                }
            }

            $run->is_post_processed = true;
            $run->save();

            $this->logger->info('Post process completed: ' . $run->id, ['count' => $count]);

            $this->tx->commit();
        } catch (\Exception $e) {
            $this->logger->error('Post process failed: ' . $run->id);
            $this->tx->rollBack();
            throw $e;
        }
    }

    private function processManual(Run $run, ActorFabric $actorFabric): void
    {
        $profileJob = $run->profileJob;
        $postJob = $run->postJob;

        if ($profileJob->job_status !== JobStatus::SUCCEEDED->value || $postJob->job_status !== JobStatus::SUCCEEDED->value) {
            return;
        }

        try {
            $this->tx->begin();

            $profileActor = $actorFabric->createActorByActorId($profileJob->actor);
            $profileItems = $profileActor->parseDatasetItems($profileJob->job_result);
            /** @var ProfileParserInterface $profileItem */
            $profileItem = $profileItems[0];

            $postActor = $actorFabric->createActorByActorId($postJob->actor);
            $postItems = $postActor->parseDatasetItems($postJob->job_result);

            $medianLikes = [];
            $medianComments = [];
            $medianViews = [];
            $medianShared = [];

            $avgLikes = 0;
            $avgComments = 0;
            $avgViews = 0;
            $avgShared = 0;

            /** @var PostParserInterface $postItem */
            foreach ($postItems as $postItem) {
                $avgLikes += $postItem->likesCount();
                $avgComments += $postItem->commentsCount();
                $avgViews += $postItem->viewsCount();
                $avgShared += $postItem->sharesCount();

                $medianLikes[] = $postItem->likesCount();
                $medianComments[] = $postItem->commentsCount();
                $medianViews[] = $postItem->viewsCount();
                $medianShared[] = $postItem->sharesCount();
            }

            $avgLikes = round($avgLikes / count($postItems));
            $avgComments = round($avgComments / count($postItems));
            $avgViews = round($avgViews / count($postItems));
            $avgShared = round($avgShared / count($postItems));

            sort($medianLikes);
            sort($medianComments);
            sort($medianViews);
            sort($medianShared);

            $medianLikes = count($medianLikes) > 2 ? $medianLikes[round(count($medianLikes) / 2)] : 0;
            $medianComments = count($medianComments) > 2 ? $medianComments[round(count($medianComments) / 2)] : 0;
            $medianViews = count($medianViews) > 2 ? $medianViews[round(count($medianViews) / 2)] : 0;
            $medianShared = count($medianShared) > 2 ? $medianShared[round(count($medianShared) / 2)] : 0;

            $viralDetector = new ViralDetector();
            $postMetric = new PostMetric();

            /** @var PostParserInterface $postItem */
            foreach ($postItems as $postItem) {
                $hash = md5($postItem->url());  

                $post = Post::where('post_hash', $hash)->where('user_id', $run->user_id)->first();
                if (!$post) {
                    $post = new Post();
                    $post->source_id = $run->source_id;
                    $post->run_id = $run->id;
                    $post->user_id = $run->user_id;
                    $post->team_id = $run->team_id;
                    $post->post_hash = $hash;
                }

                $post->post_url = $postItem->url();
                $post->post_created_at = $postItem->createdAt();
                $post->post_likes_count = $postItem->likesCount();
                $post->post_comments_count = $postItem->commentsCount();
                $post->post_views_count = $postItem->viewsCount();
                $post->post_shared_count = $postItem->sharesCount();
                $post->post_likes_count_median = $medianLikes;
                $post->post_comments_count_median = $medianComments;
                $post->post_views_count_median = $medianViews;
                $post->post_shared_count_median = $medianShared;
                $post->post_likes_count_avg = $avgLikes;
                $post->post_comments_count_avg = $avgComments;
                $post->post_views_count_avg = $avgViews;
                $post->post_shared_count_avg = $avgShared;
                $post->post_type = $postItem->type();
                $post->post_caption = $postItem->caption();
                $post->post_location = $postItem->location();
                $post->profile_followers_count = $profileItem->followersCount();
                $post->profile_url = $profileItem->url();
                $post->viral_level = $viralDetector->detect($post);

                $this->fillMetrics($postMetric, $post);

                $post->save();
            }

            $run->is_post_processed = true;
            $run->save();

            $this->logger->info('Post process completed: ' . $run->id);

            $this->tx->commit();
        } catch (\Exception $e) {
            $this->logger->error('Post process failed: ' . $run->id);
            $this->tx->rollBack();
            throw $e;
        }
    }

    private function fillMetrics(PostMetric $postMetric, Post $post): void
    {
        $post->metric_engagement_rate = $postMetric->calculateEngagementRate($post);
        $post->metric_engagement_rate_followers = $postMetric->calculateEngagementRateFollowers($post);
        $post->metric_views_followers_ratio = $postMetric->calculateViewsFollowersRatio($post);
        $post->metric_likes_views_ratio = $postMetric->calculateLikesViewsRatio($post);
        $post->metric_comments_views_ratio = $postMetric->calculateCommentsViewsRatio($post);
        $post->metric_engagement_velocity = $postMetric->calculateEngagementVelocity($post);
        $post->metric_quality_score = $postMetric->calculateQualityScore($post);
        $post->metric_viral_level = $postMetric->calculateViralLevel($post);
    }

    private function shouldSkipPost(Post $post): bool
    {
        if ($post->post_views_count < self::MIN_VIEWS) {
            return true;
        }
        if ($post->metric_engagement_rate < self::MIN_ENGAGEMENT_RATE) {
            return true;
        }
        if ($post->metric_likes_views_ratio < self::MIN_LIKES_VIEWS_RATIO) {
            return true;
        }
        if ($post->metric_quality_score < self::MIN_QUALITY_SCORE) {
            return true;
        }
        if ($post->post_created_at < $this->clock->now()->subMonths(self::MAX_AGE_MONTHS)) {
            return true;
        }

        return false;
    }

    private function isViral(Post $post): bool
    {
        if ($post->metric_viral_level === ViralLevel::VIRAL) {
            return true;
        }
        if ($post->metric_viral_level === ViralLevel::HIGH) {
            return true;
        }
        if ($post->post_views_count > self::VIRAL_VIEWS) {
            return true;
        }

        return false;
    }

    private function createViralEvent(Run $run, $postItem): void
    {
        $hash = md5($postItem->url());
        $event = Event::where('hash', $hash)->first();
        if ($event) {
            return;
        }

        $likesCount = number_format($postItem->likesCount(), 0, '.', ' ');
        $commentsCount = number_format($postItem->commentsCount(), 0, '.', ' ');
        $viewsCount = number_format($postItem->viewsCount(), 0, '.', ' ');

        $description = <<<EOT
                            Ссылка: [{$postItem->url()}]({$postItem->url()})
                            Лайков: {$likesCount}
                            Комментариев: {$commentsCount}
                            Просмотров: {$viewsCount}
                            EOT;

        Event::create([
            'source_id' => $run->source_id,
            'source_run_id' => $run->id,
            'name' => 'Новая популярная запись',
            'description' => $description,
            'is_active' => true,
            'user_id' => $run->user_id,
            'team_id' => $run->team_id,
            'hash' => $hash,
        ]);

        $this->logger->info('New trend post: ' . $postItem->url());
    }
}
