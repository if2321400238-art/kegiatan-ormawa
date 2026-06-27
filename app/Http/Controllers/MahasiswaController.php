<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_MAHASISWA)
            ->with(['ormawas' => fn ($q) => $q->orderBy('nama_ormawa')])
            ->withCount([
                'ormawas as organisasi_aktif_count' => fn ($q) => $q->where('anggota_ormawa.status', true),
            ])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'aktif');
        }

        if ($request->filled('ormawa_id')) {
            $query->whereHas('ormawas', function ($q) use ($request) {
                $q->where('ormawa.id', $request->input('ormawa_id'));
            });
        }

        $mahasiswaList = $query->paginate(10)->withQueryString();
        $ormawaList = Ormawa::orderBy('nama_ormawa')->get();

        return view('admin.mahasiswa.index', compact('mahasiswaList', 'ormawaList'));
    }

    public function create()
    {
        $ormawaList = Ormawa::orderBy('nama_ormawa')->get();

        return view('admin.mahasiswa.create', compact('ormawaList'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMahasiswa($request);

        DB::beginTransaction();
        try {
            $mahasiswa = User::create([
                'nama' => $validated['nama'],
                'nim' => $validated['nim'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_MAHASISWA,
                'no_hp' => $validated['no_hp'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            $this->syncMemberships($mahasiswa, $request);

            DB::commit();

            return redirect()->route('admin.mahasiswa.index')
                ->with('success', 'Data mahasiswa berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(User $mahasiswa)
    {
        abort_if($mahasiswa->role !== User::ROLE_MAHASISWA, 404);

        $mahasiswa->load('ormawas');
        $ormawaList = Ormawa::orderBy('nama_ormawa')->get();

        return view('admin.mahasiswa.edit', compact('mahasiswa', 'ormawaList'));
    }

    public function update(Request $request, User $mahasiswa)
    {
        abort_if($mahasiswa->role !== User::ROLE_MAHASISWA, 404);

        $validated = $this->validateMahasiswa($request, $mahasiswa);

        DB::beginTransaction();
        try {
            $data = [
                'nama' => $validated['nama'],
                'nim' => $validated['nim'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'] ?? null,
                'is_active' => $request->has('is_active'),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            $mahasiswa->update($data);
            $this->syncMemberships($mahasiswa, $request);

            DB::commit();

            return redirect()->route('admin.mahasiswa.index')
                ->with('success', 'Data mahasiswa berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(User $mahasiswa)
    {
        abort_if($mahasiswa->role !== User::ROLE_MAHASISWA, 404);

        DB::beginTransaction();
        try {
            $mahasiswa->ormawas()->detach();
            $mahasiswa->delete();

            DB::commit();

            return redirect()->route('admin.mahasiswa.index')
                ->with('success', 'Data mahasiswa berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function validateMahasiswa(Request $request, ?User $mahasiswa = null): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => ['required', 'string', 'max:30', Rule::unique('users', 'nim')->ignore($mahasiswa?->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($mahasiswa?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($mahasiswa?->id)],
            'password' => [$mahasiswa ? 'nullable' : 'required', 'string', 'min:8'],
            'no_hp' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'memberships' => 'nullable|array',
            'memberships.*.selected' => 'nullable|boolean',
            'memberships.*.jabatan' => 'nullable|string|max:100',
            'memberships.*.status' => 'nullable|boolean',
        ]);
    }

    private function syncMemberships(User $mahasiswa, Request $request): void
    {
        $memberships = [];

        foreach ($request->input('memberships', []) as $ormawaId => $membership) {
            if (empty($membership['selected'])) {
                continue;
            }

            $memberships[$ormawaId] = [
                'jabatan' => $membership['jabatan'] ?: 'anggota',
                'status' => !empty($membership['status']),
            ];
        }

        $mahasiswa->ormawas()->sync($memberships);
    }
}
