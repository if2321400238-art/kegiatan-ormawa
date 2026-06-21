<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
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
        return view('ormawa.create');
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama'  => 'required|string|max:255',
            'ketua' => 'required|string|max:255',
        ]);

        // Simpan ke database
        Ormawa::create([
            'nama'  => $request->nama,
            'ketua' => $request->ketua,
        ]);

        // Redirect kembali ke daftar ormawa
        return redirect()->route('admin.ormawa.index')
            ->with('success', 'Data ormawa berhasil ditambahkan.');
    }

}
