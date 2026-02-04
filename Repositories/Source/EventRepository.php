<?php

namespace App\Repositories\Source;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Source\Event;
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

    public function getEvents(User $user, $perPage = 100)
    {
        $query = $this->model::query()
            ->with('source')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($perPage) {

            return $query->paginate($perPage);
        }

        return $query->get();
    }
}
