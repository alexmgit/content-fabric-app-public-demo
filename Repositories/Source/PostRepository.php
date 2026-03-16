<?php

namespace App\Repositories\Source;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Source\Post;
use App\Models\User;
use App\Enums\Source\ViralLevel;

/**
 * Class PostRepository.
 */
class PostRepository extends BaseRepository
{
    protected $filters = [];

    protected $sortField = 'post_created_at';
    protected $sortDirection = 'desc';

    /**
     * @return string
     *  Return the model
     */
    public function model()
    {
        return Post::class;
    }

    public function totalCount(User $user)
    {
        return $this->model::query()
            ->where('user_id', $user->id)
            ->count();
    }

    public function weekCount(User $user)
    {
        return $this->model::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
    }

    public function getPosts(User $user, $perPage = 100)
    {
        $query = $this->model::query()
            ->with('source', 'transcribe')
            ->where('user_id', $user->id);

        if ($this->sortField === 'metric_viral_level') {
            $ids = [
                ViralLevel::LOW->value,
                ViralLevel::MEDIUM->value,
                ViralLevel::HIGH->value,
                ViralLevel::VIRAL->value,
            ];

            $query->orderByRaw('FIELD (metric_viral_level, ' . implode(', ', array_map(fn($id) => "'{$id}'", $ids)) . ') ' . $this->sortDirection);
        } 
        else
        {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        if (isset($this->filters['viral_level'])) {
            $query->where('metric_viral_level', $this->filters['viral_level']);
        }

        if (isset($this->filters['source_type'])) {
            $query->whereHas('source', function ($query) {
                $query->where('source_type', $this->filters['source_type']);
            });
        }

        if (isset($this->filters['source_id'])) {
            $query->whereHas('source', function ($query) {
                $query->where('id', $this->filters['source_id']);
            });
        }   

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function getMostPopularLastMonthPosts($limit = 10, $sortField = 'post_views_count', User $excludeUser = null)
    {
        $query = $this->model::query()
            ->with('source');
            
        if ($excludeUser)
        {
           $query->where('user_id', '!=', $excludeUser->id);
        }

        $query->where('post_created_at', '>=', now()->subMonth()->startOfMonth());
        $query->where('post_created_at', '<=', now()->subMonth()->endOfMonth());

        $query->orderBy($sortField, 'desc');

        $query->limit($limit);

        return $query->get();
    }

    public function setViralLevelFilter($filter)
    {
        $this->filters['viral_level'] = $filter;
    }

    public function setEngagementRateFilter($filter)
    {
        $this->filters['metric_engagement_rate'] = $filter;
    }

    public function setEngagementRateFollowersFilter($filter)
    {
        $this->filters['metric_engagement_rate_followers'] = $filter;
    }

    public function setViewsFollowersRatioFilter($filter)
    {
        $this->filters['metric_views_followers_ratio'] = $filter;
    }

    public function setLikesViewsRatioFilter($filter)   
    {
        $this->filters['metric_likes_views_ratio'] = $filter;
    }

    public function setCommentsViewsRatioFilter($filter)
    {
        $this->filters['metric_comments_views_ratio'] = $filter;
    }

    public function setEngagementVelocityFilter($filter)
    {
        $this->filters['metric_engagement_velocity'] = $filter;
    }

    public function setQualityScoreFilter($filter)
    {
        $this->filters['metric_quality_score'] = $filter;
    }
    
    public function setSourceTypeFilter($filter)
    {
        $this->filters['source_type'] = $filter;
    }

    public function setSourceIdFilter($filter)  
    {
        $this->filters['source_id'] = $filter;
    }   

    public function setSortField($field)
    {
        $this->sortField = $field;
    }

    public function setSortDirection($direction)
    {
        $this->sortDirection = $direction;
    }
}