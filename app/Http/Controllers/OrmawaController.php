<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\User;
use App\Models\Fakultas;
use App\Models\PengajuanKegiatan;
use Illuminate\Http\Request;


class OrmawaController extends Controller
{
    public function index()
    {
        $ormawa = Ormawa::paginate(10);
        return view('ormawa.index', compact('ormawa'));
    }

    public function create()
    {
        $dosenList = User::where('role', 'dosen')->get();
        $fakultas = Fakultas::all();
        
        $submitRoute = route('admin.ormawa.store');
        $backRoute = route('admin.ormawa.index');
        
        return view('ormawa.create', compact('dosenList', 'fakultas', 'submitRoute', 'backRoute'));
    }

    public function store(Request $request)
    {
        $ketuaUserId = $request->input('user_id');
        $request->merge(['user_id' => $ketuaUserId]);

        $request->validate([
            'nama_ormawa'  => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'pembina_user_id' => 'nullable|exists:users,id',
            'kategori_organisasi' => 'required|in:internal,eksternal',
            'tingkat_organisasi' => 'nullable|in:universitas,fakultas',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kontak' => 'nullable|string|max:20',
            'periode' => 'nullable|string|max:50',
        ]);

        if ($request->kategori_organisasi === 'internal' && !$request->filled('tingkat_organisasi')) {
            return back()->withErrors(['tingkat_organisasi' => 'Tingkat Organisasi harus diisi untuk organisasi internal.'])->withInput();
        }

        if ($request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' && !$request->filled('fakultas_id')) {
            return back()->withErrors(['fakultas_id' => 'Fakultas harus dipilih untuk organisasi tingkat fakultas.'])->withInput();
        }

        $ketuaUser = User::find($ketuaUserId);
        $pembinaUser = $request->pembina_user_id ? User::find($request->pembina_user_id) : null;

        $ormawa = Ormawa::create([
            'user_id' => $ketuaUserId,
            'nama_ormawa'  => $request->nama_ormawa,
            'ketua' => $ketuaUser->nama,
            'pembina' => $pembinaUser?->nama,
            'pembina_user_id' => $pembinaUser?->id,
            'kategori_organisasi' => $request->kategori_organisasi,
            'tingkat_organisasi' => $request->kategori_organisasi === 'internal' ? $request->tingkat_organisasi : null,
            'fakultas_id' => $request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' ? $request->fakultas_id : null,
            'kontak' => $request->kontak,
            'periode' => $request->periode,
        ]);

        // Automatically add ketua as member
        \App\Models\AnggotaOrmawa::create([
            'ormawa_id' => $ormawa->id,
            'user_id' => $ketuaUserId,
            'jabatan' => 'ketua',
            'status' => true,
        ]);

        // Redirect kembali ke daftar ormawa
        $redirectRoute = auth()->user()->role === 'bauak' ? 'bauak.ormawa.index' : 'admin.ormawa.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Data ormawa berhasil ditambahkan.');
    }

    public function show(Ormawa $pengajuan)
    {
        // Using $pengajuan as parameter name but it's actually Ormawa
        $ormawa = $pengajuan;
        return view('ormawa.show', compact('ormawa'));
    }

    public function edit(Ormawa $pengajuan)
    {
        // Using $pengajuan as parameter name but it's actually Ormawa
        $ormawa = $pengajuan;
        $dosenList = User::where('role', 'dosen')->get();
        $fakultas = Fakultas::all();
        
        $role = auth()->user()->role;
        $submitRoute = $role === 'bauak' 
            ? route('bauak.ormawa.update', $ormawa->id) 
            : route('admin.ormawa.update', $ormawa->id);
            
        $backRoute = $role === 'bauak' 
            ? route('bauak.ormawa.index') 
            : route('admin.ormawa.index');

        return view('ormawa.edit', compact('ormawa', 'dosenList', 'fakultas', 'submitRoute', 'backRoute'));
    }

    public function update(Request $request, Ormawa $pengajuan)
    {
        $ormawa = $pengajuan;
        $ketuaUserId = $request->input('user_id');
        $request->merge(['user_id' => $ketuaUserId]);

        $request->validate([
            'nama_ormawa'  => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'pembina_user_id' => 'nullable|exists:users,id',
            'kategori_organisasi' => 'required|in:internal,eksternal',
            'tingkat_organisasi' => 'nullable|in:universitas,fakultas',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kontak' => 'nullable|string|max:20',
            'periode' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->kategori_organisasi === 'internal' && !$request->filled('tingkat_organisasi')) {
            return back()->withErrors(['tingkat_organisasi' => 'Tingkat Organisasi harus diisi untuk organisasi internal.'])->withInput();
        }

        if ($request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' && !$request->filled('fakultas_id')) {
            return back()->withErrors(['fakultas_id' => 'Fakultas harus dipilih untuk organisasi tingkat fakultas.'])->withInput();
        }

        $ketuaUser = User::find($ketuaUserId);
        $pembinaUser = $request->pembina_user_id ? User::find($request->pembina_user_id) : null;

        // Update data
        $ormawa->update([
            'user_id' => $ketuaUserId,
            'nama_ormawa' => $request->nama_ormawa,
            'ketua' => $ketuaUser->nama,
            'pembina' => $pembinaUser?->nama,
            'pembina_user_id' => $pembinaUser?->id,
            'kategori_organisasi' => $request->kategori_organisasi,
            'tingkat_organisasi' => $request->kategori_organisasi === 'internal' ? $request->tingkat_organisasi : null,
            'fakultas_id' => $request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' ? $request->fakultas_id : null,
            'kontak' => $request->kontak,
            'periode' => $request->periode,
            'deskripsi' => $request->deskripsi,
        ]);

        // If ketua changed, update in anggota_ormawa
        $existingKetua = \App\Models\AnggotaOrmawa::where('ormawa_id', $ormawa->id)
            ->where('jabatan', 'ketua')
            ->first();

        if ($existingKetua && $existingKetua->user_id !== $ketuaUserId) {
            $existingKetua->update(['user_id' => $ketuaUserId]);
        } elseif (!$existingKetua) {
            \App\Models\AnggotaOrmawa::create([
                'ormawa_id' => $ormawa->id,
                'user_id' => $ketuaUserId,
                'jabatan' => 'ketua',
                'status' => true,
            ]);
        }

        $redirectRoute = auth()->user()->role === 'bauak' ? 'bauak.ormawa.index' : 'admin.ormawa.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Data ormawa berhasil diperbarui.');
    }

    public function destroy(Ormawa $pengajuan)
    {
        $ormawa = $pengajuan;
        $ormawa->delete();

        // Tentukan route index berdasarkan role user yang sedang login
        $routeIndex = auth()->user()->role === 'bauak'
            ? 'bauak.ormawa.index'
            : 'admin.ormawa.index';

        return redirect()->route($routeIndex)
            ->with('success', 'Data ormawa berhasil dihapus.');
    }

    /**
     * Search mahasiswa by name or NIM
     */
    public function searchMahasiswa(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $mahasiswa = User::where('role', 'mahasiswa')
            ->where(function ($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%")
                    ->orWhere('nim', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'nama', 'nim', 'email']);

        return response()->json($mahasiswa);
    }
}
