<?php

namespace App\Policies\Source;

use App\Models\Source\Run;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;

class RunPolicy
{
    use ChecksOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    public function delete(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    public function restore(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    public function forceDelete(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }

    public function download(User $user, Run $run): bool
    {
        return $this->isOwner($user, $run);
    }
    public function manualRun(User $user, Run $run): bool
    {
        return $this->isOwnerOrAdmin($user, $run);
    }
}
