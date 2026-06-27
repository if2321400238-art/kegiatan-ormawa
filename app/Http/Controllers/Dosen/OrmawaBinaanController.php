<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Ormawa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrmawaBinaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Ormawa::withCount('pengajuanKegiatan')
            ->where(function ($q) {
                $q->where('pembina_user_id', Auth::id())
                  ->orWhere('pembina', Auth::user()->nama);
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nama_ormawa', 'like', "%{$search}%");
        }

        $ormawaBinaan = $query->paginate(10);

        return view('dosen.ormawa.index', compact('ormawaBinaan'));
    }

    public function show(Ormawa $ormawa)
    {
        // Pastikan dosen hanya bisa melihat ormawa binaannya
        if ($ormawa->pembina_user_id !== Auth::id() && $ormawa->pembina !== Auth::user()->nama) {
            abort(403, 'Anda tidak berhak melihat data ormawa ini.');
        }

        // Ambil semua pengajuan dari ormawa ini
        $pengajuan = $ormawa->pengajuanKegiatan()->latest()->paginate(15);

        return view('dosen.ormawa.show', compact('ormawa', 'pengajuan'));
    }
}
