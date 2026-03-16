<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksOwnership
{
    private function isOwner(User $user, object $model): bool
    {
        return $user->id === $model->user_id;
    }

    private function isOwnerOrAdmin(User $user, object $model): bool
    {
        return $this->isOwner($user, $model) || $user->isAdmin();
    }
}
