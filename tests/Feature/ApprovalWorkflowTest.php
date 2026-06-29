<?php

use App\Models\Ormawa;
use App\Models\PengajuanKegiatan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTemporaryPublicDisk(): void
{
    $publicDiskRoot = sys_get_temp_dir() . '/kegiatan_public_disk';
    if (!is_dir($publicDiskRoot)) {
        mkdir($publicDiskRoot, 0777, true);
    }
    config(['filesystems.disks.public.root' => $publicDiskRoot]);
}

// helper removed: inline POST used in tests to ensure correct test context for actingAs()

it('dosen approval for internal fakultas sets menunggu_dekan and notifies dekan', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Fakultas',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. X',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'fakultas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. X']);
    $dekan = User::factory()->create(['role' => 'dekan', 'email' => 'dekan.ftik@unuja.ac.id']);
    // create fakultas and link dekan
    $fak = \App\Models\Fakultas::create(['nama' => 'Fakultas Teknik', 'dekan_user_id' => $dekan->id]);
    // assign fakultas to ormawa
    \App\Models\Ormawa::where('nama_ormawa', 'Ormawa Fakultas')->update(['fakultas_id' => $fak->id]);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);

    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    // Dosen approves
    $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'OK',
        ])
        ->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_dekan');

    // Notification to Dekan
    $hasNotif = Notifikasi::where('user_id', $dekan->id)->exists();
    expect($hasNotif)->toBeTrue();
});

it('dosen approval for internal universitas sets menunggu_bauak and notifies bauak', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Univ',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. Y',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. Y']);
    $bauak = User::factory()->create(['role' => 'bauak']);
    // fakultas for universitas case
    $fak2 = \App\Models\Fakultas::create(['nama' => 'Universitas', 'dekan_user_id' => null]);
    \App\Models\Ormawa::where('nama_ormawa', 'Ormawa Univ')->update(['fakultas_id' => $fak2->id]);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);
    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'OK',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_bauak');

    $hasNotif = Notifikasi::where('user_id', $bauak->id)->exists();
    expect($hasNotif)->toBeTrue();
});

it('dosen approval for eksternal sets menunggu_bauak and notifies bauak', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Eksternal',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. Z',
        'kategori_organisasi' => 'eksternal',
        'tingkat_organisasi' => null,
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. Z']);
    $bauak = User::factory()->create(['role' => 'bauak']);
    \App\Models\Ormawa::where('nama_ormawa', 'Ormawa Eksternal')->update(['fakultas_id' => null]);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);
    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'OK',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_bauak');

    $hasNotif = Notifikasi::where('user_id', $bauak->id)->exists();
    expect($hasNotif)->toBeTrue();
});

it('dekan approval forwards to bauak and notifies bauak', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Fakultas 2',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. A',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'fakultas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. A']);
    $dekan = User::factory()->create(['role' => 'dekan']);
    $bauak = User::factory()->create(['role' => 'bauak']);

    $fak = \App\Models\Fakultas::create(['nama' => 'Fakultas Teknik', 'dekan_user_id' => $dekan->id]);
    \App\Models\Ormawa::where('nama_ormawa', 'Ormawa Fakultas 2')->update(['fakultas_id' => $fak->id]);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);
    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    // Dosen approves first to reach menunggu_dekan
    $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'OK',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_dekan');

    // Dekan approves
    $this->actingAs($dekan)
        ->post(route('dekan.persetujuan.approve', $pengajuan), [
            'catatan' => 'Setuju',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_bauak');

    $hasNotif = Notifikasi::where('user_id', $bauak->id)->exists();
    expect($hasNotif)->toBeTrue();
});

it('dekan apt only sees and approves pengajuan from their faculty', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Teknik',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. A',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'fakultas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. A']);
    $fakTeknik = \App\Models\Fakultas::create(['nama' => 'Fakultas Teknik']);
    $fakFAI = \App\Models\Fakultas::create(['nama' => 'Fakultas Agama Islam']);

    $dekanTeknik = User::factory()->create([
        'role' => 'dekan',
        'nama' => 'Dekan Teknik',
        'fakultas_id' => $fakTeknik->id,
    ]);
    $dekanFAI = User::factory()->create([
        'role' => 'dekan',
        'nama' => 'Dekan FAI',
        'fakultas_id' => $fakFAI->id,
    ]);

    $fakTeknik->update(['dekan_user_id' => $dekanTeknik->id]);
    $fakFAI->update(['dekan_user_id' => $dekanFAI->id]);

    \App\Models\Ormawa::where('nama_ormawa', 'Ormawa Teknik')->update(['fakultas_id' => $fakTeknik->id]);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);

    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'OK',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_dekan');

    // Dekan FAI should not see this pengajuan in their list
    $this->actingAs($dekanFAI);
    $response = $this->get(route('dekan.persetujuan.index'));
    $response->assertStatus(200);
    $response->assertDontSee('Ormawa Teknik');

    // Dekan FAI should receive 403 when accessing detail
    $this->get(route('dekan.persetujuan.show', $pengajuan))->assertStatus(403);

    // Dekan Teknik can approve
    $this->actingAs($dekanTeknik)
        ->post(route('dekan.persetujuan.approve', $pengajuan), [
            'catatan' => 'Setuju',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_bauak');
});

it('bauak approval finalizes proposal and rab, and notifies warek3', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa BAUAK',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. B',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. B']);
    $bauak = User::factory()->create(['role' => 'bauak']);
    $warek3 = User::factory()->create(['role' => 'warek3']);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);
    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    // Approve by dosen to reach menunggu_bauak
    $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'OK',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_bauak');

    // BAUAK approves
    $this->actingAs($bauak)
        ->post(route('bauak.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'Lengkap',
        ])->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_warek3');

    // Check proposal & rab finalized
    $this->assertDatabaseHas('proposal', ['pengajuan_id' => $pengajuan->id, 'status' => 'final']);
    $this->assertDatabaseHas('rab', ['pengajuan_id' => $pengajuan->id, 'status' => 'final']);

    // Notification to warek3
    $hasNotif = Notifikasi::where('user_id', $warek3->id)->exists();
    expect($hasNotif)->toBeTrue();
});

it('forwards approval from warek3 to rektor and then to pp for the final decision', function () {
    createTemporaryPublicDisk();
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Final',
        'ketua' => 'Ketua',
        'pembina' => 'Dr. C',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'nama' => 'Dr. C']);
    $bauak = User::factory()->create(['role' => 'bauak']);
    $warek3 = User::factory()->create(['role' => 'warek3']);
    $rektor = User::factory()->create(['role' => 'rektor']);
    $pp = User::factory()->create(['role' => 'pp']);

    $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Test Kegiatan',
            'tujuan_kegiatan' => 'Tujuan test',
            'lokasi_kegiatan' => 'Lokasi Test',
            'tempat_pesantren' => 'Pesantren Test',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Ketua Test',
            'nama_pemohon' => 'Pemohon Test',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ])->assertStatus(302);
    $pengajuan = PengajuanKegiatan::latest()->firstOrFail();

    // Approvals up to warek3
    $this->actingAs($dosen)->post(route('dosen.verifikasi.verify', $pengajuan), ['status'=>'disetujui','catatan'=>'ok']);
    $pengajuan->refresh();
    if ($pengajuan->status === 'menunggu_dekan') {
        // If fakultas branch happened, have dekan approve
        $dekan = User::factory()->create(['role'=>'dekan']);
        $this->actingAs($dekan)->post(route('dekan.persetujuan.approve', $pengajuan), ['catatan'=>'ok']);
    }

    $this->actingAs($bauak)->post(route('bauak.verifikasi.verify', $pengajuan), ['status'=>'disetujui','catatan'=>'ok']);

    $this->actingAs($warek3)
        ->post(route('warek3.persetujuan.approve', $pengajuan), ['catatan'=>'ok'])
        ->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_rektor');

    // Notification to Rektor
    $hasNotifRektor = Notifikasi::where('user_id', $rektor->id)->exists();
    expect($hasNotifRektor)->toBeTrue();

    // Rektor approves
    $this->actingAs($rektor)
        ->post(route('rektor.persetujuan.approve', $pengajuan), ['catatan'=>'final'])
        ->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('menunggu_pp');

    expect(Notifikasi::where('user_id', $pp->id)->exists())->toBeTrue();

    $this->actingAs($pp)
        ->post(route('pp.persetujuan.approve', $pengajuan), ['catatan' => 'Persetujuan akhir'])
        ->assertStatus(302);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('disetujui');

    // Notification to Ormawa
    $hasNotifOrmawa = Notifikasi::where('user_id', $ormawaUser->id)->exists();
    expect($hasNotifOrmawa)->toBeTrue();
});

it('pp rejection records the final actor and reason', function () {
    Mail::fake();

    $ormawaUser = User::factory()->create(['role' => 'ormawa']);
    $ormawa = Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Ormawa Uji PP',
        'ketua' => 'Ketua Uji',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
    ]);
    $pp = User::factory()->create(['role' => 'pp']);
    $pengajuan = PengajuanKegiatan::create([
        'ormawa_id' => $ormawa->id,
        'judul_kegiatan' => 'Kegiatan Uji Persetujuan PP',
        'tujuan_kegiatan' => 'Pengujian',
        'lokasi_kegiatan' => 'Aula',
        'tanggal_mulai' => now()->addDay(),
        'tanggal_selesai' => now()->addDays(2),
        'ketua_pelaksana' => 'Ketua',
        'nama_pemohon' => 'Pemohon',
        'status' => 'menunggu_pp',
        'created_by_user_id' => $ormawaUser->id,
    ]);

    $this->actingAs($pp)
        ->post(route('pp.persetujuan.reject', $pengajuan), ['catatan' => 'Belum sesuai arahan pesantren'])
        ->assertRedirect(route('pp.persetujuan.index'));

    expect($pengajuan->fresh()->status)->toBe('ditolak_pp');
    $this->assertDatabaseHas('persetujuan_pp', [
        'pengajuan_id' => $pengajuan->id,
        'user_pp_id' => $pp->id,
        'status' => 'ditolak',
        'catatan' => 'Belum sesuai arahan pesantren',
    ]);
});
