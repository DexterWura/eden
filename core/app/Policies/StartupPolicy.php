<?php

namespace App\Policies;

use App\Models\Startup;
use App\Models\User;

class StartupPolicy
{
    public function manage(User $user, Startup $startup): bool
    {
        if (! $startup->exists) {
            return false;
        }

        return $startup->userCanManage($user);
    }
}
