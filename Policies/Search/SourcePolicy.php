<?php

namespace App\Policies\Search;

use App\Models\Search\Source;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;

class SourcePolicy
{
    use ChecksOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function delete(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function restore(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function forceDelete(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function manualRun(User $user, Source $source): bool
    {
        return $this->isOwnerOrAdmin($user, $source);
    }

    public function download(User $user): bool
    {
        return true;
    }
}
