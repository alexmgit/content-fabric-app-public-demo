<?php

namespace App\Repositories\Search;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Search\Search;
use App\Models\User;

/**
 * Class SearchRepository.
 */
class SearchRepository extends BaseRepository
{
    /**
     * @return string
     *  Return the model
     */
    public function model()
    {
        return Search::class;
    }

    public function getSearches(User $user)
    {
        return $this->model::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function totalActiveCount(User $user)
    {
        return $this->model::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->count();
    }
}