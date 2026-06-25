<?php

use App\Models\Ormawa;
use App\Models\PengajuanKegiatan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\actingAs;

it('processes a pengajuan from ormawa to rektor approval', function () {
    $publicDiskRoot = sys_get_temp_dir() . '/kegiatan_public_disk';
    if (!is_dir($publicDiskRoot)) {
        mkdir($publicDiskRoot, 0777, true);
    }
    config(['filesystems.disks.public.root' => $publicDiskRoot]);

    Mail::fake();

    $ormawaUser = User::factory()->create([
        'role' => 'ormawa',
        'email' => 'ormawa@example.com',
        'username' => 'ormawauser',
        'nama' => 'Ormawa User',
    ]);

    Ormawa::create([
        'user_id' => $ormawaUser->id,
        'nama_ormawa' => 'Himpunan Teknik Informatika',
        'ketua' => 'Budi Santoso',
        'pembina' => 'Dr. Sabar Priyono',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'fakultas',
    ]);

    $dosen = User::factory()->create(['role' => 'dosen', 'email' => 'dosen@example.com', 'username' => 'dosenuser']);
    $dekan = User::factory()->create(['role' => 'dekan', 'email' => 'dekan@example.com', 'username' => 'dekanuser']);
    $bauak = User::factory()->create(['role' => 'bauak', 'email' => 'bauak@example.com', 'username' => 'bauakuser']);
    $warek3 = User::factory()->create(['role' => 'warek3', 'email' => 'warek3@example.com', 'username' => 'warek3user']);
    $rektor = User::factory()->create(['role' => 'rektor', 'email' => 'rektor@example.com', 'username' => 'rektoruser']);

    $response = $this->actingAs($ormawaUser)
        ->post(route('pengajuan.store'), [
            'judul_kegiatan' => 'Seminar Karya Ilmiah',
            'tujuan_kegiatan' => 'Meningkatkan kompetensi mahasiswa',
            'lokasi_kegiatan' => 'Aula Utama',
            'tempat_pesantren' => 'Pesantren Al-Fikri',
            'tanggal_mulai' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'ketua_pelaksana' => 'Budi Santoso',
            'nama_pemohon' => 'Budi Santoso',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
            'file_rab' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
        ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('pengajuan_kegiatan', [
        'judul_kegiatan' => 'Seminar Karya Ilmiah',
        'status' => 'menunggu_dosen',
    ]);

    $pengajuan = PengajuanKegiatan::where('judul_kegiatan', 'Seminar Karya Ilmiah')->firstOrFail();

    $response = $this->actingAs($dosen)
        ->post(route('dosen.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'Siap dilanjutkan',
        ]);

    fwrite(STDERR, 'Dosen verify redirect=' . $response->headers->get('location') . "\n");
    fwrite(STDERR, 'Dosen verify status=' . $response->getStatusCode() . "\n");
    fwrite(STDERR, 'Dosen verify session_error=' . json_encode(session('error')) . "\n");
    fwrite(STDERR, 'Dosen verify session_errors=' . json_encode(session('errors') ? session('errors')->getBag('default')->messages() : []) . "\n");

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dosen.verifikasi.index'))
        ->assertStatus(302);

    $this->assertDatabaseHas('verifikasi_dosen', [
        'pengajuan_id' => $pengajuan->id,
        'status' => 'disetujui',
    ]);

    $pengajuan->refresh();
    $this->assertEquals('menunggu_dekan', $pengajuan->status);

    $this->actingAs($dekan)
        ->post(route('dekan.persetujuan.approve', $pengajuan), [
            'catatan' => 'Setuju',
        ])
        ->assertSessionHasNoErrors()
        ->assertStatus(302);

    $pengajuan->refresh();
    $this->assertEquals('menunggu_bauak', $pengajuan->status);

    $this->actingAs($bauak)
        ->post(route('bauak.verifikasi.verify', $pengajuan), [
            'status' => 'disetujui',
            'catatan' => 'Lengkap',
        ])
        ->assertSessionHasNoErrors()
        ->assertStatus(302);

    $pengajuan->refresh();
    $this->assertEquals('menunggu_warek3', $pengajuan->status);

    $this->actingAs($warek3)
        ->post(route('warek3.persetujuan.approve', $pengajuan), [
            'catatan' => 'Disetujui Warek III',
        ])
        ->assertSessionHasNoErrors()
        ->assertStatus(302);

    $pengajuan->refresh();
    $this->assertEquals('menunggu_rektor', $pengajuan->status);

    $this->actingAs($rektor)
        ->post(route('rektor.persetujuan.approve', $pengajuan), [
            'catatan' => 'Final approved',
        ])
        ->assertSessionHasNoErrors()
        ->assertStatus(302);

    $pengajuan->refresh();
    $this->assertEquals('disetujui', $pengajuan->status);
});
