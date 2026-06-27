<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FakultasController extends Controller
{
    public function index()
    {
        $fakultas = Fakultas::with('dekan')->paginate(10);
        return view('admin.fakultas.index', compact('fakultas'));
    }

    public function create()
    {
        // Ambil list user dengan role dekan yang mungkin belum jadi dekan di fakultas manapun (atau bebas pilih)
        $dekanList = User::where('role', 'dekan')->get();
        return view('admin.fakultas.create', compact('dekanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:fakultas,nama',
            'dekan_user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $fakultas = Fakultas::create([
                'nama' => $request->nama,
                'dekan_user_id' => $request->dekan_user_id,
            ]);

            if ($request->dekan_user_id) {
                // Update User table to set fakultas_id for the selected Dekan
                User::where('id', $request->dekan_user_id)->update(['fakultas_id' => $fakultas->id]);
            }

            DB::commit();
            return redirect()->route('admin.fakultas.index')->with('success', 'Fakultas berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Fakultas $fakulta)
    {
        $dekanList = User::where('role', 'dekan')->get();
        return view('admin.fakultas.edit', compact('fakulta', 'dekanList'));
    }

    public function update(Request $request, Fakultas $fakulta)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:fakultas,nama,' . $fakulta->id,
            'dekan_user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldDekanId = $fakulta->dekan_user_id;
            $newDekanId = $request->dekan_user_id;

            $fakulta->update([
                'nama' => $request->nama,
                'dekan_user_id' => $newDekanId,
            ]);

            // If dekan changed, remove fakultas_id from old dekan, set to new dekan
            if ($oldDekanId !== $newDekanId) {
                if ($oldDekanId) {
                    User::where('id', $oldDekanId)->update(['fakultas_id' => null]);
                }
                if ($newDekanId) {
                    User::where('id', $newDekanId)->update(['fakultas_id' => $fakulta->id]);
                }
            }

            DB::commit();
            return redirect()->route('admin.fakultas.index')->with('success', 'Fakultas berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Fakultas $fakulta)
    {
        DB::beginTransaction();
        try {
            if ($fakulta->dekan_user_id) {
                User::where('id', $fakulta->dekan_user_id)->update(['fakultas_id' => null]);
            }
            
            // Note: If you want to also handle Ormawa linked to this fakultas, 
            // you might want to set their fakultas_id to null as well.
            // \App\Models\Ormawa::where('fakultas_id', $fakulta->id)->update(['fakultas_id' => null]);

            $fakulta->delete();
            DB::commit();
            return redirect()->route('admin.fakultas.index')->with('success', 'Fakultas berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
