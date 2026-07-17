# Prototype Wireframe Balsamiq-Like

Dokumen ini berisi rancangan low-fidelity untuk Sistem Pengelolaan Kegiatan Ormawa UNUJA.

File prototype yang dapat dibuka dan diedit melalui diagrams.net/draw.io:

[prototype-wireframe-balsamiq.drawio](./prototype-wireframe-balsamiq.drawio)

## Cakupan layar

Prototype dibuat dalam beberapa halaman agar mudah dibaca:

1. **Auth dan Halaman Awal**: welcome, login, registrasi, lupa/reset password, verifikasi email, ganti password awal.
2. **Dashboard Role**: dashboard admin, Ormawa, mahasiswa, BAUAK, dan approver.
3. **Pengajuan Kegiatan**: daftar pengajuan, form tambah/edit, detail pengajuan, status/timeline, cetak/export.
4. **Proposal dan RAB**: daftar proposal, upload proposal/RAB, detail dan riwayat versi dokumen.
5. **Verifikasi Persetujuan**: antrian BAUAK, detail verifikasi, daftar persetujuan, review dokumen, keputusan/catatan approver.
6. **LPJ**: daftar LPJ, form LPJ, detail LPJ, realisasi anggaran, lampiran, verifikasi LPJ BAUAK.
7. **Ormawa dan Anggota**: daftar Ormawa, form Ormawa, kelola anggota, permintaan bergabung, pilih Ormawa aktif.
8. **Admin Master Data**: kelola akademik, fakultas, prodi, dekan, kaprodi, mahasiswa tersinkron, laporan admin/BAUAK.
9. **Profil Notifikasi**: profil pengguna, ubah password, koneksi Telegram, daftar/dropdown notifikasi, template email.

## Catatan

- Gaya visual sengaja dibuat seperti sketsa/Balsamiq: hitam-putih, komponen kasar, dan fokus pada struktur layar.
- Prototype ini mengikuti route Laravel, file Blade, DFD context diagram, ERD, serta alur pengajuan kegiatan.
- Jika ada view baru di aplikasi, jalankan ulang generator:

```bash
node scripts/generate_balsamiq_wireframes_drawio.mjs
```
