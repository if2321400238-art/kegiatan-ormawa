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
        $dosen = User::where('role', 'dosen')->get();
        $fakultas = Fakultas::all();
        return view('ormawa.create', compact('dosen', 'fakultas'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_ormawa'  => 'required|string|max:255',
            'ketua' => 'required|string|max:255',
            'pembina_user_id' => 'nullable|exists:users,id',
            'kategori_organisasi' => 'required|in:internal,eksternal',
            'tingkat_organisasi' => 'nullable|in:universitas,fakultas',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kontak' => 'nullable|string|max:20',
        ]);

        if ($request->kategori_organisasi === 'internal' && !$request->filled('tingkat_organisasi')) {
            return back()->withErrors(['tingkat_organisasi' => 'Tingkat Organisasi harus diisi untuk organisasi internal.'])->withInput();
        }

        if ($request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' && !$request->filled('fakultas_id')) {
            return back()->withErrors(['fakultas_id' => 'Fakultas harus dipilih untuk organisasi tingkat fakultas.'])->withInput();
        }

        $pembinaUser = $request->pembina_user_id ? User::find($request->pembina_user_id) : null;
        $pembinaName = $pembinaUser?->nama;

        Ormawa::create([
            'nama_ormawa'  => $request->nama_ormawa,
            'ketua' => $request->ketua,
            'pembina' => $pembinaName,
            'pembina_user_id' => $pembinaUser?->id,
            'kategori_organisasi' => $request->kategori_organisasi,
            'tingkat_organisasi' => $request->kategori_organisasi === 'internal' ? $request->tingkat_organisasi : null,
            'fakultas_id' => $request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' ? $request->fakultas_id : null,
            'kontak' => $request->kontak,
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
        return view('ormawa.edit', compact('ormawa', 'dosenList', 'fakultas'));
    }

    public function update(Request $request, Ormawa $pengajuan)
    {
        // Using $pengajuan as parameter name but it's actually Ormawa
        $ormawa = $pengajuan;

        // Validasi input
        $request->validate([
            'nama_ormawa'  => 'required|string|max:255',
            'ketua' => 'required|string|max:255',
            'pembina_user_id' => 'nullable|exists:users,id',
            'kategori_organisasi' => 'required|in:internal,eksternal',
            'tingkat_organisasi' => 'nullable|in:universitas,fakultas',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kontak' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->kategori_organisasi === 'internal' && !$request->filled('tingkat_organisasi')) {
            return back()->withErrors(['tingkat_organisasi' => 'Tingkat Organisasi harus diisi untuk organisasi internal.'])->withInput();
        }

        if ($request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' && !$request->filled('fakultas_id')) {
            return back()->withErrors(['fakultas_id' => 'Fakultas harus dipilih untuk organisasi tingkat fakultas.'])->withInput();
        }

        $pembinaUser = $request->pembina_user_id ? User::find($request->pembina_user_id) : null;
        $pembinaName = $pembinaUser?->nama;

        // Update data
        $ormawa->update([
            'nama_ormawa' => $request->nama_ormawa,
            'ketua' => $request->ketua,
            'pembina' => $pembinaName,
            'pembina_user_id' => $pembinaUser?->id,
            'kategori_organisasi' => $request->kategori_organisasi,
            'tingkat_organisasi' => $request->kategori_organisasi === 'internal' ? $request->tingkat_organisasi : null,
            'fakultas_id' => $request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' ? $request->fakultas_id : null,
            'kontak' => $request->kontak,
            'deskripsi' => $request->deskripsi,
        ]);

        $redirectRoute = auth()->user()->role === 'bauak' ? 'bauak.ormawa.index' : 'admin.ormawa.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Data ormawa berhasil diperbarui.');
    }

    public function destroy(Ormawa $pengajuan)
    {
        $ormawa = $pengajuan;
        $ormawa->delete();

        return redirect()->route('admin.ormawa.index')
            ->with('success', 'Data ormawa berhasil dihapus.');
    }
}
