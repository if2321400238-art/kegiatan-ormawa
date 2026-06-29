<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
                    ->orWhere('nim', 'like', "%{$search}%");
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

    public function resetPassword(User $mahasiswa): RedirectResponse
    {
        abort_if($mahasiswa->role !== User::ROLE_MAHASISWA || blank($mahasiswa->nim), 404);

        $mahasiswa->forceFill([
            'password' => Hash::make($mahasiswa->nim),
            'must_change_password' => true,
            'remember_token' => null,
        ])->save();

        return back()->with('success', "Password {$mahasiswa->nama} direset ke NIM. Mahasiswa wajib menggantinya saat login.");
    }
}
