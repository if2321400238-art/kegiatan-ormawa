<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\OrmawaMembershipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaOrmawaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $ormawas = Ormawa::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';
                $query->where('nama_ormawa', 'like', $search)
                    ->orWhere('ketua', 'like', $search)
                    ->orWhere('program_studi', 'like', $search);
            })
            ->paginate(10)
            ->withQueryString();

        $memberOrmawaIds = $user->ormawas()->pluck('ormawa_id')->toArray();
        $pendingOrmawaIds = $user->membershipRequests()
            ->where('status', OrmawaMembershipRequest::STATUS_PENDING)
            ->pluck('ormawa_id')
            ->toArray();
        $rejectedOrmawaIds = $user->membershipRequests()
            ->where('status', OrmawaMembershipRequest::STATUS_REJECTED)
            ->pluck('ormawa_id')
            ->toArray();

        return view('mahasiswa.ormawa.index', compact(
            'ormawas',
            'memberOrmawaIds',
            'pendingOrmawaIds',
            'rejectedOrmawaIds'
        ));
    }

    public function join(Ormawa $ormawa)
    {
        $user = Auth::user();

        if ($user->ormawas()->where('ormawa_id', $ormawa->id)->exists()) {
            return back()->with('warning', 'Anda sudah menjadi anggota organisasi ini.');
        }

        $existingRequest = $user->membershipRequests()->where('ormawa_id', $ormawa->id)->first();

        if ($existingRequest && $existingRequest->isPending()) {
            return back()->with('warning', 'Permintaan bergabung Anda masih menunggu persetujuan.');
        }

        if ($existingRequest) {
            $existingRequest->update([
                'status' => OrmawaMembershipRequest::STATUS_PENDING,
                'rejection_reason' => null,
                'desired_jabatan' => 'anggota',
            ]);
        } else {
            $user->membershipRequests()->create([
                'ormawa_id' => $ormawa->id,
                'status' => OrmawaMembershipRequest::STATUS_PENDING,
                'desired_jabatan' => 'anggota',
            ]);
        }

        return back()->with('success', 'Permintaan bergabung berhasil dikirim. Tunggu persetujuan ketua organisasi.');
    }

    public function requests()
    {
        $requests = Auth::user()
            ->membershipRequests()
            ->with('ormawa')
            ->latest()
            ->paginate(10);

        return view('mahasiswa.ormawa.requests', compact('requests'));
    }
}
