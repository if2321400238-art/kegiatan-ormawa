<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaDashboardController extends Controller
{
    /**
     * Display the mahasiswa dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Verify user is mahasiswa
        if ($user->role !== 'mahasiswa') {
            abort(403, 'Hanya mahasiswa yang dapat mengakses dashboard ini.');
        }

        // Get organizations where user is ketua
        $ormawaDipimpin = $user->ormawaDipimpin()->get();

        // Get organizations where user is member
        $ormawas = $user->ormawas()->wherePivot('status', true)->get();

        // Get current member organizations (via anggota_ormawa)
        $memberOrganizations = collect();
        foreach ($ormawas as $org) {
            $member = $org->anggota()
                ->where('user_id', $user->id)
                ->first();
            if ($member) {
                $org->memberData = $member;
                $memberOrganizations->push($org);
            }
        }

        $activeOrmawa = self::getActiveOrmawa();

        if ($activeOrmawa && !$user->ormawas()
            ->where('ormawa_id', $activeOrmawa->id)
            ->wherePivot('status', true)
            ->exists()) {
            session()->forget('active_ormawa_id');
            $activeOrmawa = null;
        }

        if (!$activeOrmawa && $ormawas->isNotEmpty()) {
            $activeOrmawa = $ormawas->first();
            session(['active_ormawa_id' => $activeOrmawa->id]);
        }

        return view('mahasiswa.dashboard', [
            'user' => $user,
            'ormawaDipimpin' => $ormawaDipimpin,
            'ormawas' => $ormawas,
            'memberOrganizations' => $memberOrganizations,
            'activeOrmawa' => $activeOrmawa,
        ]);
    }

    public function setActiveOrmawa(Request $request)
    {
        $validated = $request->validate([
            'ormawa_id' => 'required|integer|exists:ormawa,id',
            'redirect_to' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user->ormawas()
            ->where('ormawa_id', $validated['ormawa_id'])
            ->wherePivot('status', true)
            ->exists()) {
            abort(403, 'Anda tidak memiliki akses ke organisasi ini.');
        }

        session(['active_ormawa_id' => $validated['ormawa_id']]);

        if (!empty($validated['redirect_to'])) {
            return redirect()->route($validated['redirect_to']);
        }

        return redirect()->route('mahasiswa.dashboard');
    }

    /**
     * Get currently active ormawa
     */
    public static function getActiveOrmawa()
    {
        $activeOrmawaId = session('active_ormawa_id');

        if (!$activeOrmawaId) {
            return null;
        }

        $user = Auth::user();

        if (!$user || !$user->isMahasiswa()) {
            return null;
        }

        return $user->ormawas()
            ->where('ormawa_id', $activeOrmawaId)
            ->wherePivot('status', true)
            ->first();
    }
}
