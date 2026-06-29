<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\Ormawa;
use App\Models\User;
use App\Services\UnujaMahasiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

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

        $routePrefix = auth()->user()->role === 'bauak' ? 'bauak.' : 'admin.';
        $submitRoute = route($routePrefix.'ormawa.store');
        $backRoute = route($routePrefix.'ormawa.index');
        $searchMahasiswaRoute = route($routePrefix.'ormawa.search-mahasiswa');

        return view('ormawa.create', compact('dosenList', 'fakultas', 'submitRoute', 'backRoute', 'searchMahasiswaRoute'));
    }

    public function store(Request $request, UnujaMahasiswaService $mahasiswaService)
    {
        $request->validate([
            'nama_ormawa' => 'required|string|max:255',
            'ketua_nim' => 'nullable|string|max:30|required_without:user_id',
            'user_id' => 'nullable|exists:users,id|required_without:ketua_nim',
            'pembina_user_id' => 'nullable|exists:users,id',
            'kategori_organisasi' => 'required|in:internal,eksternal',
            'tingkat_organisasi' => 'nullable|in:universitas,fakultas',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kontak' => 'nullable|string|max:20',
            'periode' => 'nullable|string|max:50',
        ]);

        $ketuaUser = $this->resolveKetua($request, $mahasiswaService);
        if (! $ketuaUser) {
            return back()->withErrors(['ketua_nim' => 'Data ketua tidak dapat diverifikasi melalui API mahasiswa.'])->withInput();
        }
        $ketuaUserId = $ketuaUser->id;

        if ($request->kategori_organisasi === 'internal' && ! $request->filled('tingkat_organisasi')) {
            return back()->withErrors(['tingkat_organisasi' => 'Tingkat Organisasi harus diisi untuk organisasi internal.'])->withInput();
        }

        if ($request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' && ! $request->filled('fakultas_id')) {
            return back()->withErrors(['fakultas_id' => 'Fakultas harus dipilih untuk organisasi tingkat fakultas.'])->withInput();
        }

        $pembinaUser = $request->pembina_user_id ? User::find($request->pembina_user_id) : null;

        $ormawa = Ormawa::create([
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
        $searchMahasiswaRoute = $role === 'bauak'
            ? route('bauak.ormawa.search-mahasiswa')
            : route('admin.ormawa.search-mahasiswa');

        return view('ormawa.edit', compact('ormawa', 'dosenList', 'fakultas', 'submitRoute', 'backRoute', 'searchMahasiswaRoute'));
    }

    public function update(Request $request, Ormawa $pengajuan, UnujaMahasiswaService $mahasiswaService)
    {
        $ormawa = $pengajuan;
        $request->validate([
            'nama_ormawa' => 'required|string|max:255',
            'ketua_nim' => 'nullable|string|max:30|required_without:user_id',
            'user_id' => 'nullable|exists:users,id|required_without:ketua_nim',
            'pembina_user_id' => 'nullable|exists:users,id',
            'kategori_organisasi' => 'required|in:internal,eksternal',
            'tingkat_organisasi' => 'nullable|in:universitas,fakultas',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kontak' => 'nullable|string|max:20',
            'periode' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        $ketuaUser = $this->resolveKetua($request, $mahasiswaService);
        if (! $ketuaUser) {
            return back()->withErrors(['ketua_nim' => 'Data ketua tidak dapat diverifikasi melalui API mahasiswa.'])->withInput();
        }
        $ketuaUserId = $ketuaUser->id;

        if ($request->kategori_organisasi === 'internal' && ! $request->filled('tingkat_organisasi')) {
            return back()->withErrors(['tingkat_organisasi' => 'Tingkat Organisasi harus diisi untuk organisasi internal.'])->withInput();
        }

        if ($request->kategori_organisasi === 'internal' && $request->tingkat_organisasi === 'fakultas' && ! $request->filled('fakultas_id')) {
            return back()->withErrors(['fakultas_id' => 'Fakultas harus dipilih untuk organisasi tingkat fakultas.'])->withInput();
        }

        $pembinaUser = $request->pembina_user_id ? User::find($request->pembina_user_id) : null;

        DB::transaction(function () use ($ormawa, $request, $ketuaUser, $ketuaUserId, $pembinaUser) {
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

            \App\Models\AnggotaOrmawa::where('ormawa_id', $ormawa->id)
                ->where('jabatan', 'ketua')
                ->where('user_id', '!=', $ketuaUserId)
                ->delete();

            \App\Models\AnggotaOrmawa::updateOrCreate([
                'ormawa_id' => $ormawa->id,
                'user_id' => $ketuaUserId,
            ], [
                'jabatan' => 'ketua',
                'status' => true,
            ]);
        });

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
    public function searchMahasiswa(Request $request, UnujaMahasiswaService $mahasiswaService)
    {
        $query = $request->get('q', '');

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            return response()->json(collect($mahasiswaService->search($query))->take(10)->values());
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'API mahasiswa UNUJA sedang tidak dapat diakses. Silakan coba lagi.',
            ], 503);
        }
    }

    private function resolveKetua(Request $request, UnujaMahasiswaService $mahasiswaService): ?User
    {
        try {
            if ($request->filled('ketua_nim')) {
                return $mahasiswaService->syncUserByNim($request->string('ketua_nim')->toString());
            }

            return User::find($request->input('user_id'));
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
