<?php

namespace App\Http\Controllers;

use App\Models\Ormawa;
use App\Models\OrmawaMembershipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrmawaMembershipRequestController extends Controller
{
    public function index(Ormawa $ormawa)
    {
        $user = Auth::user();

        if (!$user->isKetuaOf($ormawa)) {
            abort(403, 'Hanya ketua organisasi yang dapat mengelola permintaan bergabung.');
        }

        $requests = $ormawa->membershipRequests()
            ->where('status', OrmawaMembershipRequest::STATUS_PENDING)
            ->with('user')
            ->paginate(10);

        return view('ormawa.requests.index', compact('ormawa', 'requests'));
    }

    public function approve(Ormawa $ormawa, OrmawaMembershipRequest $membershipRequest)
    {
        $user = Auth::user();

        if (!$user->isKetuaOf($ormawa)) {
            abort(403, 'Hanya ketua organisasi yang dapat menyetujui permintaan ini.');
        }

        if ($membershipRequest->ormawa_id !== $ormawa->id) {
            abort(404);
        }

        if (!$membershipRequest->isPending()) {
            return back()->with('warning', 'Permintaan ini bukan dalam status menunggu.');
        }

        $member = $ormawa->users()->where('users.id', $membershipRequest->user_id)->first();

        if ($member) {
            $ormawa->users()->updateExistingPivot($membershipRequest->user_id, [
                'jabatan' => 'anggota',
                'status' => true,
            ]);
        } else {
            $ormawa->users()->attach($membershipRequest->user_id, [
                'jabatan' => 'anggota',
                'status' => true,
            ]);
        }

        $membershipRequest->update([
            'status' => OrmawaMembershipRequest::STATUS_APPROVED,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Permintaan bergabung telah disetujui.');
    }

    public function reject(Request $request, Ormawa $ormawa, OrmawaMembershipRequest $membershipRequest)
    {
        $user = Auth::user();

        if (!$user->isKetuaOf($ormawa)) {
            abort(403, 'Hanya ketua organisasi yang dapat menolak permintaan ini.');
        }

        if ($membershipRequest->ormawa_id !== $ormawa->id) {
            abort(404);
        }

        if (!$membershipRequest->isPending()) {
            return back()->with('warning', 'Permintaan ini bukan dalam status menunggu.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $membershipRequest->update([
            'status' => OrmawaMembershipRequest::STATUS_REJECTED,
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Permintaan bergabung telah ditolak.');
    }
}
