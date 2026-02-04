<?php

namespace App\Repositories\Source;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Source\Run;
use App\Models\User;

/**
 * Class RunRepository.
 */
class RunRepository extends BaseRepository
{
    /**
     * @return string|Model
     *  Return the model
     */
    public function model()
    {
        return Run::class;
    }

    public function getRuns(User $user)
    {
        return $this->model->where('user_id', $user->id)
            ->with('source')
            ->with('profileJob')
            ->with('postJob')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
