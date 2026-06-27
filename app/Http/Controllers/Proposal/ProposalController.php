<?php

namespace App\Http\Controllers\Proposal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proposal\StoreProposalRequest;
use App\Http\Requests\Proposal\UpdateProposalRequest;
use App\Models\PengajuanKegiatan;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PengajuanKegiatan::class, 'proposal');
    }

    public function index()
    {
        $pengajuan = PengajuanKegiatan::query()
            ->where('ormawa_id', optional(Auth::user()->ormawa)->id)
            ->latest()
            ->paginate(10);

        return view('proposal.index', compact('pengajuan'));
    }

    public function create()
    {
        return view('proposal.create');
    }

    public function store(StoreProposalRequest $request)
    {
        $proposal = PengajuanKegiatan::create(array_merge(
            $request->validated(),
            [
                'ormawa_id' => Auth::user()->ormawa->id ?? null,
                'created_by_user_id' => Auth::id(),
                'status' => 'draft',
            ]
        ));

        return redirect()->route('proposal-kegiatan.show', $proposal)
            ->with('success', 'Proposal berhasil dibuat.');
    }

    public function show(PengajuanKegiatan $proposal)
    {
        return view('proposal.show', compact('proposal'));
    }

    public function edit(PengajuanKegiatan $proposal)
    {
        return view('proposal.create', compact('proposal'));
    }

    public function update(UpdateProposalRequest $request, PengajuanKegiatan $proposal)
    {
        $proposal->update($request->validated());

        return redirect()->route('proposal-kegiatan.show', $proposal)
            ->with('success', 'Proposal berhasil diperbarui.');
    }

    public function destroy(PengajuanKegiatan $proposal)
    {
        $proposal->delete();

        return redirect()->route('proposal-kegiatan.index')
            ->with('success', 'Proposal berhasil dihapus.');
    }
}
