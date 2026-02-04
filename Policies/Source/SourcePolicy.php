<?php

namespace App\Policies\Source;

use App\Models\Source\Source;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SourcePolicy
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
    public function view(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if (config('app.is_use_plans')) {
            $count = Source::where('user_id', $user->id)->count();
            $subscription = $user->planSubscription('main');
            $max = $subscription ? $subscription->getFeatureValue('max_sources') : 0;
            if ((int)$count >= (int)$max) {
                return Response::deny('Вы достигли максимального количества источников. Подключите тариф, чтобы увеличить лимит');
            }
        }

        return Response::allow();
    }

    public function parse(User $user, Source $source): Response
    {
        if (config('app.is_use_plans')) {
            $subscription = $user->planSubscription('main');
            $max = $subscription ? $subscription->getFeatureValue('max_sources') : 0;
            if ((int)$max <= 0) {
                return Response::deny('Вы достигли максимального количества источников. Подключите тариф, чтобы увеличить лимит');
            }
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function manualRun(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source) || $user->isAdmin();
    }

    private function isOwner(User $user, Source $source): bool
    {
        return $user->id === $source->user_id;
    }
}
