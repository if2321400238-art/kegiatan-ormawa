<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrmawaAnggotaController extends Controller
{
    /**
     * Display a listing of anggota for a specific ormawa.
     */
    public function index(Ormawa $ormawa)
    {
        $anggota = $ormawa->users()
            ->withPivot('jabatan', 'aktif', 'created_at')
            ->paginate(10);

        return view('ormawa.anggota.index', compact('ormawa', 'anggota'));
    }

    /**
     * Show the form for creating a new anggota.
     */
    public function create(Ormawa $ormawa)
    {
        // Get users yang belum menjadi anggota di ormawa ini
        $existingUserIds = $ormawa->users()->pluck('users.id')->toArray();
        $availableUsers = User::whereNotIn('id', $existingUserIds)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $jabatanOptions = [
            'ketua' => 'Ketua',
            'wakil_ketua' => 'Wakil Ketua',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'anggota' => 'Anggota',
        ];

        return view('ormawa.anggota.create', compact('ormawa', 'availableUsers', 'jabatanOptions'));
    }

    /**
     * Store a newly created anggota in storage.
     */
    public function store(Request $request, Ormawa $ormawa)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jabatan' => 'required|in:ketua,wakil_ketua,sekretaris,bendahara,anggota',
            'aktif' => 'boolean',
        ]);

        // Check if user is already a member
        $existingMember = $ormawa->users()->where('users.id', $validated['user_id'])->first();
        if ($existingMember) {
            return back()->withErrors(['user_id' => 'User sudah menjadi anggota ormawa ini.'])->withInput();
        }

        $ormawa->users()->attach($validated['user_id'], [
            'jabatan' => $validated['jabatan'],
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.ormawa.anggota.index', $ormawa)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified anggota.
     */
    public function edit(Ormawa $ormawa, User $user)
    {
        // Verify user is a member of this ormawa
        $member = $ormawa->users()->where('users.id', $user->id)->first();
        if (!$member) {
            abort(404);
        }

        $jabatanOptions = [
            'ketua' => 'Ketua',
            'wakil_ketua' => 'Wakil Ketua',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'anggota' => 'Anggota',
        ];

        return view('ormawa.anggota.edit', compact('ormawa', 'user', 'jabatanOptions', 'member'));
    }

    /**
     * Update the specified anggota in storage.
     */
    public function update(Request $request, Ormawa $ormawa, User $user)
    {
        // Verify user is a member of this ormawa
        $member = $ormawa->users()->where('users.id', $user->id)->first();
        if (!$member) {
            abort(404);
        }

        $validated = $request->validate([
            'jabatan' => 'required|in:ketua,wakil_ketua,sekretaris,bendahara,anggota',
            'aktif' => 'boolean',
        ]);

        $ormawa->users()->updateExistingPivot($user->id, [
            'jabatan' => $validated['jabatan'],
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.ormawa.anggota.index', $ormawa)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified anggota from storage.
     */
    public function destroy(Ormawa $ormawa, User $user)
    {
        // Verify user is a member of this ormawa
        $member = $ormawa->users()->where('users.id', $user->id)->first();
        if (!$member) {
            abort(404);
        }

        $ormawa->users()->detach($user->id);

        return redirect()->route('admin.ormawa.anggota.index', $ormawa)
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
