<?php

namespace App\Policies\Search;

use App\Models\Search\Source;
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
    public function create(User $user): bool
    {
        return true;
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

    public function download(User $user): bool
    {
        return true;
    }

    private function isOwner(User $user, Source $source): bool
    {
        return $user->id === $source->user_id;
    }
}
