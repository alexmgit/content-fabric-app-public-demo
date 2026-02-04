<?php

namespace App\Repositories\Source;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Source\Source;
use App\Models\User;

/**
 * Class SourceRepository.
 */
class SourceRepository extends BaseRepository
{
    /**
     * @return string
     *  Return the model
     */
    public function model()
    {
        return Source::class;
    }

    public function getSources(User $user)
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