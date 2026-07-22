<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DekanController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'dekan')->with('fakultas');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $dekanList = $query->paginate(10);
        return view('admin.dekan.index', compact('dekanList'));
    }

    public function create()
    {
        $fakultasList = Fakultas::all();
        return view('admin.dekan.create', compact('fakultasList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'no_hp' => 'nullable|string|max:20',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $dekan = User::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dekan',
                'no_hp' => $request->no_hp,
                'fakultas_id' => $request->fakultas_id,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            if ($request->fakultas_id) {
                // Remove any existing dekan from that fakultas just in case
                User::where('role', 'dekan')
                    ->where('fakultas_id', $request->fakultas_id)
                    ->where('id', '!=', $dekan->id)
                    ->update(['fakultas_id' => null]);
                    
                // Link fakultas to this new dekan
                Fakultas::where('id', $request->fakultas_id)->update(['dekan_user_id' => $dekan->id]);
            }

            DB::commit();
            return redirect()->route('admin.dekan.index')->with('success', 'Akun dekan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(User $dekan)
    {
        if ($dekan->role !== 'dekan') {
            abort(404);
        }

        $fakultasList = Fakultas::all();
        return view('admin.dekan.edit', compact('dekan', 'fakultasList'));
    }

    public function update(Request $request, User $dekan)
    {
        if ($dekan->role !== 'dekan') {
            abort(404);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($dekan->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($dekan->id)],
            'password' => 'nullable|string|min:8',
            'no_hp' => 'nullable|string|max:20',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $oldFakultasId = $dekan->fakultas_id;
            $newFakultasId = $request->fakultas_id;

            $data = [
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'fakultas_id' => $newFakultasId,
                'is_active' => $request->has('is_active'),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $dekan->update($data);

            // Handle relation updates if fakultas was changed
            if ($oldFakultasId != $newFakultasId) {
                if ($oldFakultasId) {
                    // Remove dekan from old fakultas if it matches this dekan
                    Fakultas::where('id', $oldFakultasId)
                            ->where('dekan_user_id', $dekan->id)
                            ->update(['dekan_user_id' => null]);
                }
                if ($newFakultasId) {
                    // Make sure other dekans don't point to this fakultas
                    User::where('role', 'dekan')
                        ->where('fakultas_id', $newFakultasId)
                        ->where('id', '!=', $dekan->id)
                        ->update(['fakultas_id' => null]);
                        
                    Fakultas::where('id', $newFakultasId)->update(['dekan_user_id' => $dekan->id]);
                }
            }

            DB::commit();
            return redirect()->route('admin.dekan.index')->with('success', 'Akun dekan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(User $dekan)
    {
        if ($dekan->role !== 'dekan') {
            abort(404);
        }

        DB::beginTransaction();
        try {
            if ($dekan->fakultas_id) {
                Fakultas::where('id', $dekan->fakultas_id)
                        ->where('dekan_user_id', $dekan->id)
                        ->update(['dekan_user_id' => null]);
            }
            
            $dekan->delete();
            DB::commit();
            return redirect()->route('admin.dekan.index')->with('success', 'Akun dekan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
