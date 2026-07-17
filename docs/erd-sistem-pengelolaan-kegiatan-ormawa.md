# ERD Sistem Pengelolaan Kegiatan Ormawa UNUJA

ERD berikut disusun berdasarkan struktur tabel aktif pada migration dan model Laravel sistem. Diagram dibuat dengan gaya konseptual seperti contoh: entitas ditampilkan sebagai kotak, relasi sebagai diamond, serta kardinalitas `1` dan `N` pada hubungan antar entitas.

File gambar yang dapat dibuka dan disunting melalui draw.io tersedia di:

[erd-sistem-pengelolaan-kegiatan-ormawa.drawio](./erd-sistem-pengelolaan-kegiatan-ormawa.drawio)

## Kamus data

Keterangan:

- `*` = primary key
- `**` = foreign key

a) **User** : id*, fakultas_id**, prodi_id**, username, nim, nidn, email, email_verified_at, password, must_change_password, role, program_studi, jabatan_fungsional, nama, no_hp, telegram_id, is_active, remember_token, created_at, updated_at, deleted_at.

b) **Fakultas** : id*, dekan_user_id**, nama, created_at, updated_at.

c) **ProgramStudi** : id*, fakultas_id**, kaprodi_user_id**, nama, kode, profile_url, is_lainnya, created_at, updated_at.

d) **Ormawa** : id*, user_id**, fakultas_id**, prodi_id**, nama_ormawa, ketua, periode, kategori_organisasi, tingkat_organisasi, program_studi, kop_surat, kontak, deskripsi, created_at, updated_at, deleted_at.

e) **AnggotaOrmawa** : id*, ormawa_id**, user_id**, jabatan, status, created_at, updated_at.

f) **PengajuanKegiatan** : id*, ormawa_id**, created_by_user_id**, updated_by_user_id**, judul_kegiatan, tujuan_kegiatan, lokasi_kegiatan, tempat_pesantren, tanggal_mulai, tanggal_selesai, ketua_pelaksana, nama_pemohon, status, catatan, created_at, updated_at, deleted_at.

g) **Proposal** : id*, pengajuan_id**, file_proposal, status, versi, created_at, updated_at.

h) **Rab** : id*, pengajuan_id**, file_rab, total_anggaran, status, versi, created_at, updated_at.

i) **PersetujuanKaprodi** : id*, pengajuan_id**, user_kaprodi_id**, catatan, status, tanggal_acc, created_at, updated_at.

j) **PersetujuanDekan** : id*, pengajuan_id**, user_dekan_id**, catatan, status, tanggal_acc, created_at, updated_at.

k) **VerifikasiBauak** : id*, pengajuan_id**, user_bauak_id**, catatan, status, tanggal_verifikasi, created_at, updated_at.

l) **PersetujuanWarek3** : id*, pengajuan_id**, user_warek3_id**, catatan, status, tanggal_acc, signature_path, created_at, updated_at.

m) **PersetujuanRektor** : id*, pengajuan_id**, user_rektor_id**, catatan, status, tanggal_acc, created_at, updated_at.

n) **PersetujuanPp** : id*, pengajuan_id**, user_pp_id**, catatan, status, tanggal_acc, created_at, updated_at.

o) **LaporanPertanggungjawaban** : id*, pengajuan_id**, created_by**, verified_by**, ringkasan_pelaksanaan, hasil_kegiatan, kendala, tanggal_pelaksanaan_mulai, tanggal_pelaksanaan_selesai, jumlah_peserta, realisasi_anggaran, sisa_anggaran, file_laporan, status, catatan_verifikator, submitted_at, verified_at, created_at, updated_at.

p) **LpjRealisasiAnggaran** : id*, lpj_id**, uraian, anggaran_rencana, anggaran_realisasi, keterangan, created_at, updated_at.

q) **LpjLampiran** : id*, lpj_id**, jenis, nama_file, file_path, created_at, updated_at.

r) **LpjVersiDokumen** : id*, lpj_id**, uploaded_by**, versi, nama_file, file_path, created_at, updated_at.

s) **VerifikasiLpj** : id*, lpj_id**, user_bauak_id**, status, catatan, tanggal_verifikasi, created_at, updated_at.

t) **Notifikasi** : id*, user_id**, telegram_id, judul, pesan, delivery_channels, delivery_status, link, tipe, dibaca, dibaca_pada, read_at, created_at, updated_at.

u) **TelegramConnectionCode** : id*, user_id**, code_hash, code_digest, attempts, expires_at, created_at, updated_at.

v) **LogAktivitas** : id*, user_id**, aktivitas, modul, subjek_type, subjek_id, deskripsi, ip_address, user_agent, created_at, updated_at.

## Struktur database

Database adalah pusat data yang dipakai oleh Sistem Pengelolaan Kegiatan Ormawa UNUJA. Database menyimpan data pengguna, Ormawa, keanggotaan, struktur akademik, pengajuan kegiatan, dokumen proposal, RAB, persetujuan berjenjang, LPJ, notifikasi, koneksi Telegram, dan log aktivitas.

Database menggunakan MySQL melalui migration Laravel. Struktur tabel dibuat saling berelasi menggunakan primary key dan foreign key agar data pengajuan, dokumen, riwayat persetujuan, serta laporan pertanggungjawaban tetap konsisten dan tidak menimbulkan redundansi berlebihan. Relasi utama sistem berpusat pada tabel `users`, `ormawa`, dan `pengajuan_kegiatan`, kemudian bercabang ke dokumen proposal/RAB, tabel persetujuan, LPJ, notifikasi, dan log aktivitas.

## Ringkasan relasi utama

| Relasi | Kardinalitas | Keterangan |
|---|---:|---|
| Fakultas - ProgramStudi | 1 : N | Satu fakultas memiliki banyak program studi. |
| Fakultas - User | 1 : N | User tertentu, seperti dekan atau mahasiswa, dapat dikaitkan dengan fakultas. |
| ProgramStudi - User | 1 : N | User dapat dikaitkan dengan program studi. |
| User - Ormawa | 1 : N | User dengan role Ormawa/ketua dapat memiliki atau memimpin Ormawa. |
| Ormawa - AnggotaOrmawa | 1 : N | Satu Ormawa memiliki banyak anggota. |
| User - AnggotaOrmawa | 1 : N | Satu user dapat menjadi anggota beberapa Ormawa melalui tabel anggota. |
| Ormawa - PengajuanKegiatan | 1 : N | Satu Ormawa dapat membuat banyak pengajuan kegiatan. |
| PengajuanKegiatan - Proposal | 1 : N | Satu pengajuan dapat memiliki versi dokumen proposal. |
| PengajuanKegiatan - Rab | 1 : N | Satu pengajuan dapat memiliki versi dokumen RAB. |
| PengajuanKegiatan - tabel persetujuan/verifikasi | 1 : N | Setiap tahap approval menyimpan riwayat keputusan dan catatan. |
| PengajuanKegiatan - LaporanPertanggungjawaban | 1 : 1 | Satu pengajuan yang selesai memiliki satu LPJ utama. |
| LaporanPertanggungjawaban - detail LPJ | 1 : N | LPJ memiliki realisasi anggaran, lampiran, versi dokumen, dan riwayat verifikasi. |
| User - Notifikasi | 1 : N | Satu user menerima banyak notifikasi. |
| User - LogAktivitas | 1 : N | Aktivitas user dicatat sebagai audit trail. |
| User - TelegramConnectionCode | 1 : 1 | Satu user dapat memiliki satu kode koneksi Telegram aktif. |

## Batasan ERD

- Diagram draw.io menampilkan tabel inti dan relasi yang paling penting agar tetap terbaca.
- Tabel teknis Laravel seperti `sessions`, `cache`, `jobs`, `failed_jobs`, dan `password_reset_tokens` tidak dimasukkan ke ERD utama karena tidak merepresentasikan proses bisnis pengelolaan kegiatan Ormawa.
- Tabel approval dipisahkan per aktor karena implementasi sistem menyimpan riwayat keputusan Kaprodi, Dekan, BAUAK, Wakil Rektor III, Rektor, dan Kepala/Wakil PP pada tabel berbeda.
- Pada gambar ERD, foreign key aktor approval yang mengarah ke tabel `users` diringkas dalam kotak catatan agar garis relasi tidak terlalu panjang dan tidak saling bertumpuk. Detail field tetap dicantumkan lengkap pada kamus data.
- Field `program_studi` pada `users` dan `ormawa` tetap dicantumkan karena masih ada pada migration, walaupun relasi terstruktur juga tersedia melalui `prodi_id` ke tabel `program_studi`.
