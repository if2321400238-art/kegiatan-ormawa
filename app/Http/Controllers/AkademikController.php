<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\Ormawa;
use App\Models\ProgramStudi;
use App\Models\User;

class AkademikController extends Controller
{
    public function index()
    {
        $stats = [
            'fakultas' => Fakultas::count(),
            'dekan' => User::where('role', 'dekan')->count(),
            'prodi' => ProgramStudi::where('is_lainnya', false)->count(),
            'kaprodi' => User::where('role', 'kaprodi')->whereNotNull('prodi_id')->count(),
        ];
        $programStudi = ProgramStudi::with(['fakultas', 'kaprodi'])->withCount('ormawas')->orderBy('nama')->take(8)->get();
        return view('admin.akademik.index', compact('stats', 'programStudi'));
    }
}
