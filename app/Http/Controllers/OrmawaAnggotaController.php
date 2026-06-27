<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Http\Request;

class OrmawaAnggotaController extends Controller
{
    /**
     * Display a listing of anggota for a specific ormawa.
     */
    public function index(Ormawa $ormawa)
    {
        $this->authorize('manageMembers', $ormawa);

        $anggota = $ormawa->users()
            ->withPivot(['jabatan', 'status', 'created_at'])
            ->paginate(10);

        return view('ormawa.anggota.index', compact('ormawa', 'anggota'));
    }

    /**
     * Show the form for creating a new anggota.
     */
    public function create(Request $request, Ormawa $ormawa)
    {
        $this->authorize('manageMembers', $ormawa);

        $search = $request->query('search');
        $availableUsers = User::where('role', 'mahasiswa')
            ->whereNotIn('id', function ($query) use ($ormawa) {
                $query->select('user_id')
                    ->from('anggota_ormawa')
                    ->where('ormawa_id', $ormawa->id);
            })
            ->where('is_active', true)
            ->where('id', '!=', $ormawa->user_id)
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nim', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->limit(30)
            ->get();

        $jabatanOptions = [
            'ketua' => 'Ketua',
            'wakil_ketua' => 'Wakil Ketua',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'anggota' => 'Anggota',
        ];

        return view('ormawa.anggota.create', compact('ormawa', 'availableUsers', 'jabatanOptions', 'search'));
    }

    /**
     * Search mahasiswa to add as anggota.
     */
    public function search(Request $request, Ormawa $ormawa)
    {
        $this->authorize('manageMembers', $ormawa);

        $validated = $request->validate([
            'search' => 'required|string|min:2',
        ]);

        $users = User::where('role', 'mahasiswa')
            ->whereNotIn('id', function ($query) use ($ormawa) {
                $query->select('user_id')
                    ->from('anggota_ormawa')
                    ->where('ormawa_id', $ormawa->id);
            })
            ->where('is_active', true)
            ->where('id', '!=', $ormawa->user_id)
            ->where(function ($query) use ($validated) {
                $query->where('nim', 'like', "%{$validated['search']}%")
                    ->orWhere('nama', 'like', "%{$validated['search']}%");
            })
            ->orderBy('nama')
            ->limit(20)
            ->get(['id', 'nama', 'nim', 'email']);

        return response()->json(['data' => $users]);
    }

    /**
     * Store a newly created anggota in storage.
     */
    public function store(Request $request, Ormawa $ormawa)
    {
        $this->authorize('manageMembers', $ormawa);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jabatan' => 'required|in:ketua,wakil_ketua,sekretaris,bendahara,anggota',
            'status' => 'nullable|boolean',
        ]);

        if ($validated['user_id'] === $ormawa->user_id) {
            return back()->withErrors(['user_id' => 'Mahasiswa tersebut adalah ketua organisasi.'])->withInput();
        }

        $existingMember = $ormawa->users()->where('users.id', $validated['user_id'])->first();
        if ($existingMember) {
            return back()->withErrors(['user_id' => 'User sudah menjadi anggota ormawa ini.'])->withInput();
        }

        $ormawa->users()->attach($validated['user_id'], [
            'jabatan' => $validated['jabatan'],
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('ormawa.anggota.index', $ormawa)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified anggota.
     */
    public function edit(Ormawa $ormawa, User $user)
    {
        $this->authorize('manageMembers', $ormawa);

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
        $this->authorize('manageMembers', $ormawa);

        $member = $ormawa->users()->where('users.id', $user->id)->first();
        if (!$member) {
            abort(404);
        }

        $validated = $request->validate([
            'jabatan' => 'required|in:ketua,wakil_ketua,sekretaris,bendahara,anggota',
            'status' => 'nullable|boolean',
        ]);

        $ormawa->users()->updateExistingPivot($user->id, [
            'jabatan' => $validated['jabatan'],
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('ormawa.anggota.index', $ormawa)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified anggota from storage.
     */
    public function destroy(Ormawa $ormawa, User $user)
    {
        $this->authorize('manageMembers', $ormawa);

        $member = $ormawa->users()->where('users.id', $user->id)->first();
        if (!$member) {
            abort(404);
        }

        if ($ormawa->isKetua($user)) {
            return back()->withErrors(['user_id' => 'Ketua organisasi tidak dapat dihapus dari anggota.']);
        }

        $ormawa->users()->detach($user->id);

        return redirect()->route('ormawa.anggota.index', $ormawa)
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
