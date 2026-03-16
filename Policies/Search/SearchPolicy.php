<?php

namespace App\Policies\Search;

use App\Models\Search\Search;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;
use App\Policies\Concerns\ChecksPlanFeature;
use Illuminate\Auth\Access\Response;

class SearchPolicy
{
    use ChecksOwnership;
    use ChecksPlanFeature;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    public function create(User $user): Response
    {
        return $this->denyIfReachedFeatureLimit(
            $user,
            $user->searches()->count(),
            'max_searches',
            'Вы достигли максимального количества поисковых запросов. Подключите тариф, чтобы увеличить лимит'
        );
    }

    public function parse(User $user, Search $search): Response
    {
        return $this->denyIfFeatureUnavailable(
            $user,
            'max_searches',
            'Вы достигли максимального количества поисковых запросов. Подключите тариф, чтобы увеличить лимит'
        );
    }

    public function update(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    public function delete(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    public function restore(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    public function forceDelete(User $user, Search $search): bool
    {
        return $this->isOwner($user, $search);
    }

    public function manualRun(User $user, Search $search): bool
    {
        return $this->isOwnerOrAdmin($user, $search);
    }

    public function download(User $user): bool
    {
        return true;
    }
}
