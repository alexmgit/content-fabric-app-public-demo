<?php

namespace App\Policies\Search;

use App\Models\Search\Search;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SearchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if (config('app.is_use_plans')) {
            $count = Search::where('user_id', $user->id)->count();
            $subscription = $user->planSubscription('main');
            $max = $subscription ? $subscription->getFeatureValue('max_searches') : 0;
            if ((int)$count >= (int)$max) {
                return Response::deny('Вы достигли максимального количества поисковых запросов. Подключите тариф, чтобы увеличить лимит');
            }
        }

        return Response::allow();
    }

    public function parse(User $user, Search $search): Response
    {
        if (config('app.is_use_plans')) {
            $subscription = $user->planSubscription('main');
            $max = $subscription ? $subscription->getFeatureValue('max_searches') : 0;
            if ((int)$max <= 0) {
                return Response::deny('Вы достигли максимального количества поисковых запросов. Подключите тариф, чтобы увеличить лимит');
            }
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    public function manualRun(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search) || $user->isAdmin();
    }

    public function download(User $user): bool
    {
        return true;
    }

    private function isOwner(User $user, Search $search): bool
    {
        return $user->id === $search->user_id;
    }
}
