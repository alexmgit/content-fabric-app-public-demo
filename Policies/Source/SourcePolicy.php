<?php

namespace App\Policies\Source;

use App\Models\Source\Source;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;
use App\Policies\Concerns\ChecksPlanFeature;
use Illuminate\Auth\Access\Response;

class SourcePolicy
{
    use ChecksOwnership;
    use ChecksPlanFeature;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Source $source): bool
    {
        return $this->isOwner($user, $source);
    }

    public function create(User $user): Response
    {
        return $this->denyIfReachedFeatureLimit(
            $user,
            $user->sources()->count(),
            'max_sources',
            'Вы достигли максимального количества источников. Подключите тариф, чтобы увеличить лимит'
        );
    }

    public function parse(User $user, Source $source): Response
    {
        return $this->denyIfFeatureUnavailable(
            $user,
            'max_sources',
            'Вы достигли максимального количества источников. Подключите тариф, чтобы увеличить лимит'
        );
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
}
