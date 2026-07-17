<?php

use App\Models\LaporanPertanggungjawaban;
use App\Models\Notifikasi;
use App\Models\Ormawa;
use App\Models\PengajuanKegiatan;
use App\Models\Rab;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

it('allows ormawa to submit an LPJ and BAUAK to accept it', function () {
    Storage::fake('public');
    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    $ormawa = Ormawa::create(['user_id' => $ormawaUser->id, 'nama_ormawa' => 'BEM Uji', 'ketua' => 'Ketua']);
    $pengajuan = PengajuanKegiatan::create([
        'ormawa_id' => $ormawa->id, 'created_by_user_id' => $ormawaUser->id,
        'judul_kegiatan' => 'Kegiatan Uji LPJ', 'tujuan_kegiatan' => 'Uji alur', 'lokasi_kegiatan' => 'Aula',
        'tanggal_mulai' => now()->subDays(2), 'tanggal_selesai' => now()->subDay(),
        'ketua_pelaksana' => 'Ketua', 'nama_pemohon' => 'Ketua', 'status' => 'disetujui',
    ]);
    Rab::create(['pengajuan_id' => $pengajuan->id, 'file_rab' => 'rab.pdf', 'total_anggaran' => 1000000, 'status' => 'final']);

    $this->actingAs($ormawaUser)->post(route('lpj.store', $pengajuan), [
        'ringkasan_pelaksanaan' => 'Kegiatan berjalan lancar', 'hasil_kegiatan' => 'Target tercapai',
        'tanggal_pelaksanaan_mulai' => now()->subDays(2)->format('Y-m-d'),
        'tanggal_pelaksanaan_selesai' => now()->subDay()->format('Y-m-d'), 'jumlah_peserta' => 50,
        'file_laporan' => UploadedFile::fake()->create('lpj.pdf', 100, 'application/pdf'),
        'uraian' => ['Konsumsi'], 'anggaran_rencana' => [1000000], 'anggaran_realisasi' => [900000],
        'keterangan' => ['Sesuai bukti'], 'aksi' => 'ajukan',
    ])->assertRedirect();

    $lpj = LaporanPertanggungjawaban::firstOrFail();
    expect($lpj->status)->toBe('diajukan')->and((float) $lpj->sisa_anggaran)->toBe(100000.0);

    $bauak = User::factory()->create(['role' => 'bauak']);
    $this->actingAs($bauak)->post(route('bauak.lpj.decide', $lpj), [
        'status' => 'diterima', 'catatan' => 'Dokumen lengkap',
    ])->assertRedirect(route('bauak.lpj.index'));

    expect($lpj->refresh()->status)->toBe('diterima')
        ->and($pengajuan->refresh()->status)->toBe('selesai');
    $this->assertDatabaseHas('verifikasi_lpj', ['lpj_id' => $lpj->id, 'status' => 'diterima']);
});


it('notifies active mahasiswa members and BAUAK through telegram when an LPJ is submitted', function () {
    Storage::fake('public');
    Mail::fake();
    config(['services.telegram.bot_token' => 'test-token', 'services.telegram.api_url' => 'https://api.telegram.test']);
    Http::fake(['api.telegram.test/*' => Http::response(['ok' => true, 'result' => []])]);

    $ormawaUser = User::factory()->create(['role' => User::ROLE_ORMAWA]);
    $mahasiswa = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'telegram_id' => '111']);
    $bauak = User::factory()->create(['role' => User::ROLE_BAUAK, 'telegram_id' => '222']);
    $ormawa = Ormawa::create(['user_id' => $ormawaUser->id, 'nama_ormawa' => 'BEM Telegram LPJ', 'ketua' => 'Ketua']);
    $ormawa->users()->attach($mahasiswa->id, ['jabatan' => 'anggota', 'status' => true]);
    $pengajuan = PengajuanKegiatan::create([
        'ormawa_id' => $ormawa->id, 'created_by_user_id' => $ormawaUser->id,
        'judul_kegiatan' => 'Kegiatan LPJ Telegram', 'tujuan_kegiatan' => 'Uji notifikasi', 'lokasi_kegiatan' => 'Aula',
        'tanggal_mulai' => now()->subDays(2), 'tanggal_selesai' => now()->subDay(),
        'ketua_pelaksana' => 'Ketua', 'nama_pemohon' => 'Ketua', 'status' => 'disetujui',
    ]);
    Rab::create(['pengajuan_id' => $pengajuan->id, 'file_rab' => 'rab.pdf', 'total_anggaran' => 1000000, 'status' => 'final']);

    $this->actingAs($ormawaUser)->post(route('lpj.store', $pengajuan), [
        'ringkasan_pelaksanaan' => 'Kegiatan berjalan lancar', 'hasil_kegiatan' => 'Target tercapai',
        'tanggal_pelaksanaan_mulai' => now()->subDays(2)->format('Y-m-d'),
        'tanggal_pelaksanaan_selesai' => now()->subDay()->format('Y-m-d'), 'jumlah_peserta' => 50,
        'file_laporan' => UploadedFile::fake()->create('lpj.pdf', 100, 'application/pdf'),
        'uraian' => ['Konsumsi'], 'anggaran_rencana' => [1000000], 'anggaran_realisasi' => [900000],
        'keterangan' => ['Sesuai bukti'], 'aksi' => 'ajukan',
    ])->assertRedirect();

    $mahasiswaNotification = Notifikasi::where('user_id', $mahasiswa->id)->where('judul', 'LPJ Diajukan')->firstOrFail();
    $bauakNotification = Notifikasi::where('user_id', $bauak->id)->where('judul', 'LPJ Menunggu Verifikasi')->firstOrFail();

    expect($mahasiswaNotification->delivery_channels['telegram'])->toBe('sent')
        ->and($bauakNotification->delivery_channels['telegram'])->toBe('sent');
    Http::assertSentCount(2);
});

it('keeps the activity open when BAUAK requests an LPJ revision', function () {
    Mail::fake();
    config(['services.telegram.bot_token' => 'test-token', 'services.telegram.api_url' => 'https://api.telegram.test']);
    Http::fake(['api.telegram.test/*' => Http::response(['ok' => true, 'result' => []])]);

    $owner = User::factory()->create(['role' => 'ormawa']);
    $mahasiswa = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'telegram_id' => '333']);
    $ormawa = Ormawa::create(['user_id' => $owner->id, 'nama_ormawa' => 'UKM Uji', 'ketua' => 'Ketua']);
    $ormawa->users()->attach($mahasiswa->id, ['jabatan' => 'anggota', 'status' => true]);
    $pengajuan = PengajuanKegiatan::create(['ormawa_id' => $ormawa->id, 'judul_kegiatan' => 'Uji Revisi', 'lokasi_kegiatan' => 'Aula',
        'tanggal_mulai' => now(), 'tanggal_selesai' => now(), 'ketua_pelaksana' => 'A', 'nama_pemohon' => 'A', 'status' => 'disetujui']);
    $lpj = LaporanPertanggungjawaban::create(['pengajuan_id' => $pengajuan->id, 'ringkasan_pelaksanaan' => 'Ringkas', 'hasil_kegiatan' => 'Hasil',
        'tanggal_pelaksanaan_mulai' => now(), 'tanggal_pelaksanaan_selesai' => now(), 'jumlah_peserta' => 10,
        'realisasi_anggaran' => 0, 'sisa_anggaran' => 0, 'file_laporan' => 'lpj.pdf', 'status' => 'diajukan', 'created_by' => $owner->id]);
    $bauak = User::factory()->create(['role' => 'bauak']);

    $this->actingAs($bauak)->post(route('bauak.lpj.decide', $lpj), ['status' => 'revisi', 'catatan' => 'Bukti transaksi kurang'])->assertRedirect();

    $notification = Notifikasi::where('user_id', $mahasiswa->id)->where('judul', 'Status LPJ Diperbarui')->firstOrFail();

    expect($lpj->refresh()->status)->toBe('revisi')
        ->and($pengajuan->refresh()->status)->toBe('disetujui')
        ->and($notification->delivery_channels['telegram'])->toBe('sent');
});

it('lists approved activities with their LPJ availability for the owner', function () {
    $owner = User::factory()->create(['role' => 'ormawa']);
    $ormawa = Ormawa::create(['user_id' => $owner->id, 'nama_ormawa' => 'BEM Daftar LPJ', 'ketua' => 'Ketua']);
    $withoutLpj = PengajuanKegiatan::create([
        'ormawa_id' => $ormawa->id,
        'judul_kegiatan' => 'Kegiatan Belum LPJ',
        'lokasi_kegiatan' => 'Aula',
        'tanggal_mulai' => now(),
        'tanggal_selesai' => now(),
        'ketua_pelaksana' => 'A',
        'nama_pemohon' => 'A',
        'status' => 'disetujui',
    ]);

    $this->actingAs($owner)->get(route('lpj.index'))
        ->assertOk()
        ->assertSee('Kegiatan Belum LPJ')
        ->assertSee('Belum LPJ')
        ->assertSee(route('lpj.create', $withoutLpj), false)
        ->assertSee('Tambah LPJ');
});

it('shows an empty LPJ page instead of forbidden for an owner without an organization', function () {
    $owner = User::factory()->create(['role' => 'ormawa']);

    $this->actingAs($owner)->get(route('lpj.index'))
        ->assertOk()
        ->assertSee('Belum ada organisasi yang terhubung');
});
