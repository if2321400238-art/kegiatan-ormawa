<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\User;
use App\Services\UnujaMahasiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

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
    public function create(Ormawa $ormawa)
    {
        $this->authorize('manageMembers', $ormawa);

        $jabatanOptions = [
            'ketua' => 'Ketua',
            'wakil_ketua' => 'Wakil Ketua',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'anggota' => 'Anggota',
        ];

        return view('ormawa.anggota.create', compact('ormawa', 'jabatanOptions'));
    }

    /**
     * Search mahasiswa to add as anggota.
     */
    public function search(Request $request, Ormawa $ormawa, UnujaMahasiswaService $mahasiswaService)
    {
        $this->authorize('manageMembers', $ormawa);

        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'cariby' => 'nullable|in:nim,nama',
        ]);

        try {
            $students = $mahasiswaService->search(
                $validated['search'],
                $validated['cariby'] ?? null
            );

            $localUsers = User::withTrashed()
                ->whereIn('nim', collect($students)->pluck('nim'))
                ->get()
                ->keyBy('nim');
            $memberIds = $ormawa->users()->pluck('users.id')->all();

            $data = collect($students)->take(20)->map(function ($student) use ($localUsers, $memberIds) {
                $localUser = $localUsers->get($student['nim']);
                $student['id'] = $localUser?->id;
                $student['already_member'] = $localUser && in_array($localUser->id, $memberIds, true);

                return $student;
            })->values();

            return response()->json(['data' => $data]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'API mahasiswa UNUJA sedang tidak dapat diakses. Silakan coba lagi.',
                'data' => [],
            ], 503);
        }
    }

    /**
     * Store a newly created anggota in storage.
     */
    public function store(Request $request, Ormawa $ormawa, UnujaMahasiswaService $mahasiswaService)
    {
        $this->authorize('manageMembers', $ormawa);

        $validated = $request->validate([
            'nim' => 'nullable|string|max:30|required_without:user_id',
            'user_id' => 'nullable|exists:users,id|required_without:nim',
            'jabatan' => 'required|in:ketua,wakil_ketua,sekretaris,bendahara,anggota',
            'status' => 'nullable|boolean',
        ]);

        try {
            $user = filled($validated['nim'] ?? null)
                ? $mahasiswaService->syncUserByNim($validated['nim'])
                : User::findOrFail($validated['user_id']);
        } catch (Throwable $exception) {
            report($exception);

            $message = $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'API mahasiswa UNUJA sedang tidak dapat diakses. Silakan coba lagi.';

            return back()->withErrors(['nim' => $message])->withInput();
        }

        if ($user->id === $ormawa->user_id) {
            return back()->withErrors(['user_id' => 'Mahasiswa tersebut adalah ketua organisasi.'])->withInput();
        }

        $existingMember = $ormawa->users()->where('users.id', $user->id)->first();
        if ($existingMember) {
            return back()->withErrors(['user_id' => 'User sudah menjadi anggota ormawa ini.'])->withInput();
        }

        DB::transaction(function () use ($ormawa, $user, $validated, $request) {
            $ormawa->users()->attach($user->id, [
                'jabatan' => $validated['jabatan'],
                'status' => $request->boolean('status', true),
            ]);
        });

        return redirect()->route($this->memberRoute('index'), $ormawa)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    private function memberRoute(string $action): string
    {
        return match (true) {
            request()->routeIs('admin.*') => "admin.ormawa.anggota.{$action}",
            request()->routeIs('bauak.*') => "bauak.ormawa.anggota.{$action}",
            default => "ormawa.anggota.{$action}",
        };
    }

    /**
     * Show the form for editing the specified anggota.
     */
    public function edit(Ormawa $ormawa, User $user)
    {
        $this->authorize('manageMembers', $ormawa);

        $member = $ormawa->users()->where('users.id', $user->id)->first();
        if (! $member) {
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
        if (! $member) {
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
        if (! $member) {
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
