<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class KaprodiController extends Controller
{
    public function index(Request $request)
    {
        $kaprodiList = User::where('role','kaprodi')->whereNotNull('prodi_id')->with('programStudiKaprodi.fakultas')->when($request->filled('search'), fn($q)=>$q->where(fn($search)=>$search->where('nama','like','%'.$request->search.'%')->orWhere('email','like','%'.$request->search.'%')))->orderBy('nama')->paginate(15)->withQueryString();
        return view('admin.kaprodi.index', compact('kaprodiList'));
    }
    public function create() { return $this->formView(new User); }
    public function edit(User $kaprodi) { abort_unless($kaprodi->role==='kaprodi',404); return $this->formView($kaprodi); }
    public function store(Request $request) { return $this->save($request, new User); }
    public function update(Request $request, User $kaprodi) { abort_unless($kaprodi->role==='kaprodi',404); return $this->save($request,$kaprodi); }
    public function destroy(User $kaprodi) { abort_unless($kaprodi->role==='kaprodi',404); ProgramStudi::where('kaprodi_user_id',$kaprodi->id)->update(['kaprodi_user_id'=>null]); $kaprodi->delete(); return back()->with('success','Akun Kaprodi berhasil dihapus.'); }
    private function formView(User $kaprodi) { return view('admin.kaprodi.form',['kaprodi'=>$kaprodi,'programStudi'=>ProgramStudi::with('fakultas')->where('is_lainnya',false)->orderBy('nama')->get()]); }
    private function save(Request $request, User $kaprodi)
    {
        $data=$request->validate(['nama'=>'required|string|max:255','username'=>['required','string','max:255',Rule::unique('users')->ignore($kaprodi->id)],'email'=>['required','email',Rule::unique('users')->ignore($kaprodi->id)],'password'=>[$kaprodi->exists?'nullable':'required','string','min:8'],'no_hp'=>'nullable|string|max:20','prodi_id'=>'required|exists:program_studi,id','is_active'=>'nullable|boolean']);
        $prodi=ProgramStudi::findOrFail($data['prodi_id']);
        DB::transaction(function() use($kaprodi,$data,$prodi){ $old=$kaprodi->prodi_id; $kaprodi->fill(['nama'=>$data['nama'],'username'=>$data['username'],'email'=>$data['email'],'role'=>'kaprodi','no_hp'=>$data['no_hp']??null,'prodi_id'=>$prodi->id,'fakultas_id'=>$prodi->fakultas_id,'program_studi'=>$prodi->nama,'is_active'=>(bool)($data['is_active']??false)]); if(!empty($data['password']))$kaprodi->password=Hash::make($data['password']); $kaprodi->save(); if($old && $old!=$prodi->id)ProgramStudi::whereKey($old)->where('kaprodi_user_id',$kaprodi->id)->update(['kaprodi_user_id'=>null]); $previous=User::where('role','kaprodi')->where('prodi_id',$prodi->id)->where('id','!=',$kaprodi->id)->pluck('id'); User::whereIn('id',$previous)->update(['prodi_id'=>null]); ProgramStudi::whereIn('kaprodi_user_id',$previous)->update(['kaprodi_user_id'=>null]); $prodi->update(['kaprodi_user_id'=>$kaprodi->id]); });
        return redirect()->route('admin.kaprodi.index')->with('success','Akun Kaprodi berhasil disimpan.');
    }
}
