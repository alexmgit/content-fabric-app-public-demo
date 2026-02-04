<?php

namespace App\Policies\Source;

use App\Models\Source\Run;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RunPolicy
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
    public function view(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
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
    public function update(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    public function download(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    private function isOwner(User $user, Run $run): bool
    {
        return $user->id === $run->user_id;
    }

    public function manualRun(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run) || $user->isAdmin();
    }
}
