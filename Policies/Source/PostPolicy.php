<?php

namespace App\Policies\Source;

use App\Models\Source\Post;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;

class PostPolicy
{
    use ChecksOwnership;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post);
    }

    public function restore(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post);
    }

    public function download(User $user): bool
    {
        return true;
    }
}
