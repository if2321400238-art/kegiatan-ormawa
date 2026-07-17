<?php

namespace App\Http\Controllers;

use App\Models\LaporanPertanggungjawaban;
use App\Models\User;
use App\Models\VerifikasiLpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiLpjController extends Controller
{
    private const NOTIFICATION_CHANNELS = ['telegram', 'email', 'in_app'];

    public function index(Request $request)
    {
        $query = LaporanPertanggungjawaban::with('pengajuan.ormawa')->where('status', 'diajukan')->latest('submitted_at');
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('pengajuan', fn ($q) => $q->where('judul_kegiatan', 'like', "%{$search}%"));
        }

        return view('bauak.lpj.index', ['lpjs' => $query->paginate(12)->withQueryString()]);
    }

    public function decide(Request $request, LaporanPertanggungjawaban $lpj)
    {
        abort_unless($lpj->status === 'diajukan', 422);
        $data = $request->validate([
            'status' => ['required', 'in:revisi,diterima,ditolak'],
            'catatan' => [$request->status === 'diterima' ? 'nullable' : 'required', 'string', 'max:3000'],
        ]);

        DB::transaction(function () use ($request, $lpj, $data) {
            VerifikasiLpj::create(['lpj_id' => $lpj->id, 'user_bauak_id' => $request->user()->id,
                'status' => $data['status'], 'catatan' => $data['catatan'] ?? null, 'tanggal_verifikasi' => now()]);
            $lpj->update(['status' => $data['status'], 'catatan_verifikator' => $data['catatan'] ?? null,
                'verified_by' => $request->user()->id, 'verified_at' => now()]);
            if ($data['status'] === 'diterima') {
                $lpj->pengajuan()->update(['status' => 'selesai', 'updated_by_user_id' => $request->user()->id]);
            }
            $lpj->loadMissing('pengajuan.ormawa.user');
            foreach ($this->organizationRecipients($lpj) as $user) {
                sendNotification($user, $data['status'] === 'diterima' ? 'LPJ Diterima' : 'Status LPJ Diperbarui',
                    "LPJ kegiatan '{$lpj->pengajuan->judul_kegiatan}' berstatus {$data['status']}.".(! empty($data['catatan']) ? " Catatan: {$data['catatan']}" : ''),
                    $data['status'] === 'diterima' ? 'success' : 'warning', route('lpj.show', $lpj), self::NOTIFICATION_CHANNELS);
            }
        });

        return redirect()->route('bauak.lpj.index')->with('success', 'Keputusan LPJ berhasil disimpan.');
    }

    private function organizationRecipients(LaporanPertanggungjawaban $lpj)
    {
        $ormawa = $lpj->pengajuan->ormawa;

        return collect([$ormawa->user])
            ->merge($ormawa->users()
                ->wherePivot('status', true)
                ->where('users.role', User::ROLE_MAHASISWA)
                ->where('users.is_active', true)
                ->get())
            ->filter(fn ($user) => $user && $user->is_active)
            ->unique('id')
            ->values();
    }
}
