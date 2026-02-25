<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasBloggingAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasBloggingAccess();
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->hasBloggingAccess() && ($user->isAdmin() || $post->user_id === $user->id);
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->hasBloggingAccess() && ($user->isAdmin() || $post->user_id === $user->id);
    }
}
