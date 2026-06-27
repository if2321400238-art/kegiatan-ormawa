<?php

namespace App\Http\Requests\Proposal;

use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\PengajuanKegiatan::class);
    }

    public function rules(): array
    {
        return [
            'judul_kegiatan' => ['required', 'string', 'max:255'],
            'tujuan_kegiatan' => ['required', 'string'],
            'lokasi_kegiatan' => ['required', 'string', 'max:255'],
            'tempat_pesantren' => ['nullable', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'ketua_pelaksana' => ['nullable', 'string', 'max:255'],
            'nama_pemohon' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
