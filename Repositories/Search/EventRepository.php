<?php

namespace App\Repositories\Search;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Search\Event;
use App\Models\User;

/**
 * Class EventRepository.
 */
class EventRepository extends BaseRepository
{
    /**
     * @return string
     *  Return the model
     */
    public function model()
    {
        return Event::class;
    }

    public function getEvents(User $user)
    {
        return $this->model::query()
            ->with('search')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        }
}
