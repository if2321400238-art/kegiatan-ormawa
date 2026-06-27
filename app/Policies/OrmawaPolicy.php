<?php

namespace App\Policies;

use App\Models\Ormawa;
use App\Models\User;

class OrmawaPolicy
{
    public function manageMembers(User $user, Ormawa $ormawa): bool
    {
        if ($user->isAdmin() || $user->isBauak()) {
            return true;
        }

        return $ormawa->isKetua($user);
    }
}
