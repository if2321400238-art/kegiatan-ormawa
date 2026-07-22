<?php

namespace App\Policies;

use App\Models\PengajuanKegiatan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengajuanKegiatanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role === 'ormawa';
    }

    public function view(User $user, PengajuanKegiatan $proposal): bool
    {
        return $user->role === 'ormawa' && $proposal->ormawa_id === optional($user->ormawa)->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'ormawa';
    }

    public function update(User $user, PengajuanKegiatan $proposal): bool
    {
        return $user->role === 'ormawa'
            && $proposal->ormawa_id === optional($user->ormawa)->id
            && in_array($proposal->status, ['draft', 'ditolak']);
    }

    public function delete(User $user, PengajuanKegiatan $proposal): bool
    {
        return $user->role === 'ormawa'
            && $proposal->ormawa_id === optional($user->ormawa)->id
            && $proposal->status === 'draft';
    }

    public function submit(User $user, PengajuanKegiatan $proposal): bool
    {
        return $user->role === 'ormawa'
            && $proposal->ormawa_id === optional($user->ormawa)->id
            && $proposal->status === 'draft';
    }

    public function approve(User $user, PengajuanKegiatan $proposal): bool
    {
        return false;
    }

    public function reject(User $user, PengajuanKegiatan $proposal): bool
    {
        return false;
    }
}
