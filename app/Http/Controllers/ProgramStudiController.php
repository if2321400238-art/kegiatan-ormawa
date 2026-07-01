<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramStudiController extends Controller
{
    public function index(Request $request)
    {
        $programStudi = ProgramStudi::with(['fakultas', 'kaprodi'])->withCount('ormawas')
            ->when($request->filled('search'), fn ($q) => $q->where('nama', 'like', '%'.$request->search.'%')->orWhere('kode', 'like', '%'.$request->search.'%'))
            ->orderBy('nama')->paginate(15)->withQueryString();
        return view('admin.prodi.index', compact('programStudi'));
    }

    public function create() { return $this->formView(new ProgramStudi); }
    public function edit(ProgramStudi $prodi) { return $this->formView($prodi); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $prodi = ProgramStudi::create($data + ['is_lainnya' => false]);
        $this->syncKaprodi($prodi, $data['kaprodi_user_id'] ?? null);
        return redirect()->route('admin.prodi.index')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function update(Request $request, ProgramStudi $prodi)
    {
        $data = $this->validated($request, $prodi);
        $prodi->update($data);
        $this->syncKaprodi($prodi, $data['kaprodi_user_id'] ?? null);
        return redirect()->route('admin.prodi.index')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(ProgramStudi $prodi)
    {
        abort_if($prodi->is_lainnya, 422, 'Opsi Prodi Lainnya tidak dapat dihapus.');
        $prodi->delete();
        return redirect()->route('admin.prodi.index')->with('success', 'Program studi berhasil dihapus.');
    }

    private function formView(ProgramStudi $prodi)
    {
        return view('admin.prodi.form', ['prodi' => $prodi, 'fakultas' => Fakultas::orderBy('nama')->get(), 'kaprodi' => User::where('role', 'kaprodi')->orderBy('nama')->get()]);
    }

    private function validated(Request $request, ?ProgramStudi $prodi = null): array
    {
        return $request->validate(['nama'=>'required|string|max:255','kode'=>['required','string','max:50',Rule::unique('program_studi')->ignore($prodi?->id)],'fakultas_id'=>'required|exists:fakultas,id','kaprodi_user_id'=>'nullable|exists:users,id','profile_url'=>'nullable|url|max:255']);
    }

    private function syncKaprodi(ProgramStudi $prodi, $userId): void
    {
        User::where('prodi_id', $prodi->id)->where('id', '!=', $userId)->update(['prodi_id'=>null]);
        if ($userId) {
            ProgramStudi::where('kaprodi_user_id', $userId)->where('id', '!=', $prodi->id)->update(['kaprodi_user_id'=>null]);
            User::whereKey($userId)->update(['role'=>'kaprodi','prodi_id'=>$prodi->id,'fakultas_id'=>$prodi->fakultas_id,'program_studi'=>$prodi->nama]);
        }
    }
}
