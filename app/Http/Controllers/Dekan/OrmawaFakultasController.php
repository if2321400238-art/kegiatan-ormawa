<?php

namespace App\Http\Controllers\Dekan;

use App\Http\Controllers\Controller;
use App\Models\Ormawa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrmawaFakultasController extends Controller
{
    public function index(Request $request)
    {
        $fakultasId = Auth::user()->fakultas_id;

        $query = Ormawa::withCount('pengajuanKegiatan')
            ->where('fakultas_id', $fakultasId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nama_ormawa', 'like', "%{$search}%");
        }

        $ormawaFakultas = $query->paginate(10);

        return view('dekan.ormawa.index', compact('ormawaFakultas'));
    }

    public function show(Ormawa $ormawa)
    {
        // Pastikan dekan hanya bisa melihat ormawa di fakultasnya
        if ($ormawa->fakultas_id !== Auth::user()->fakultas_id) {
            abort(403, 'Anda tidak berhak melihat data ormawa ini.');
        }

        // Ambil semua pengajuan dari ormawa ini
        $pengajuan = $ormawa->pengajuanKegiatan()->latest()->paginate(15);

        return view('dekan.ormawa.show', compact('ormawa', 'pengajuan'));
    }
}
