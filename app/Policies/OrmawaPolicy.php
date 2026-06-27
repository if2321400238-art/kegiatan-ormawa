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

        if ($ormawa->isKetua($user)) {
            return true;
        }

        if (! $user->isMahasiswa()) {
            return false;
        }

        return $ormawa->users()
            ->where('users.id', $user->id)
            ->wherePivot('jabatan', 'ketua')
            ->wherePivot('status', true)
            ->exists();
    }
}
