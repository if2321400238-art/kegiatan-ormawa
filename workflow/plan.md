# PLAN.md

# Sistem Informasi Pengajuan dan Persetujuan Kegiatan Organisasi Mahasiswa (Ormawa) Berbasis Web

## Deskripsi Sistem

Sistem Informasi Pengajuan dan Persetujuan Kegiatan Organisasi Mahasiswa (Ormawa) merupakan aplikasi berbasis web yang digunakan untuk mendigitalisasi proses pengajuan kegiatan organisasi mahasiswa di lingkungan universitas.

Sistem ini bertujuan untuk menggantikan proses pengajuan proposal yang masih dilakukan secara manual menjadi proses yang terintegrasi, transparan, terdokumentasi, dan mudah dipantau oleh seluruh pihak yang terlibat.

Penggunaan sistem dibatasi pada: mahasiswa/organisasi kemahasiswaan (Ormawa), Dosen Pembina, Dekan, Wakil Rektor III, Rektor, Kepala/Wakil PP, dan Biro Administrasi Umum dan Kemahasiswaan (BAUAK) Universitas Nurul Jadid, dengan hak akses masing-masing.

Pengajuan kegiatan dilakukan secara bertahap mulai dari Ormawa, Dosen Pembina, Dekan (khusus Ormawa tingkat fakultas), BAUAK, Wakil Rektor III, hingga Rektor sebagai pemberi persetujuan akhir.

---

# Tujuan Sistem

- Mempermudah proses pengajuan kegiatan Ormawa.
- Mempercepat proses verifikasi dan persetujuan.
- Mengurangi penggunaan dokumen fisik.
- Memudahkan monitoring status pengajuan.
- Menyediakan riwayat pengajuan yang terdokumentasi.
- Meningkatkan transparansi proses persetujuan kegiatan.

---

# Aktor Sistem

## 1. Admin

Hak akses:

- Mengelola pengguna.
- Mengelola data Ormawa.
- Mengelola data fakultas.
- Mengelola data jabatan.
- Mengelola tahun akademik.
- Mengelola kategori kegiatan.
- Mengelola sistem.

---

## 2. Ormawa

Hak akses:

- Membuat pengajuan kegiatan.
- Mengunggah proposal.
- Mengunggah surat permohonan.
- Mengunggah RAB.
- Mengubah data sebelum diverifikasi.
- Melihat status pengajuan.
- Melihat riwayat pengajuan.

---

## 3. Dosen Pembina

Hak akses:

- Melihat pengajuan Ormawa binaan.
- Memberikan catatan.
- Menyetujui pengajuan.
- Menolak pengajuan.

---

## 4. Dekan

Hak akses:

- Melihat pengajuan Ormawa tingkat fakultas.
- Memberikan catatan.
- Menyetujui pengajuan.
- Menolak pengajuan.

Catatan:

Tahap ini hanya berlaku untuk Ormawa tingkat fakultas.

---

## 5. BAUAK

Hak akses:

- Memverifikasi administrasi.
- Memverifikasi proposal.
- Memverifikasi RAB.
- Memberikan catatan.
- Menyetujui pengajuan.
- Menolak pengajuan.

---

## 6. Wakil Rektor III

Hak akses:

- Meninjau kegiatan.
- Memberikan catatan.
- Menyetujui pengajuan.
- Menolak pengajuan.

---

## 7. Rektor

Hak akses:

- Melihat seluruh pengajuan.
- Memberikan keputusan akhir.
- Menyetujui pengajuan.
- Menolak pengajuan.

---

# Jenis Ormawa

## Ormawa Tingkat Fakultas

Contoh:

- BEM Fakultas
- HMPS

Alur:

Ormawa
↓
Dosen Pembina
↓
Dekan
↓
BAUAK
↓
Wakil Rektor III
↓
Rektor

---

## Ormawa Tingkat Universitas

Contoh:

- BEM Universitas
- UKM

Alur:

Ormawa
↓
Dosen Pembina
↓
BAUAK
↓
Wakil Rektor III
↓
Rektor

---

# Dynamic Workflow

Sistem harus memiliki mekanisme alur dinamis.

Jika:

jenis_ormawa = fakultas

Maka:

Ormawa
→ Dosen Pembina
→ Dekan
→ BAUAK
→ Wakil Rektor III
→ Rektor

Jika:

jenis_ormawa = universitas

Maka:

Ormawa
→ Dosen Pembina
→ BAUAK
→ Wakil Rektor III
→ Rektor

---

# Status Pengajuan

Draft

Pengajuan dibuat namun belum dikirim.

Diajukan

Pengajuan telah dikirim.

Menunggu Dosen Pembina

Menunggu verifikasi dosen pembina.

Menunggu Dekan

Menunggu persetujuan dekan.

Menunggu BAUAK

Menunggu verifikasi BAUAK.

Menunggu Wakil Rektor III

Menunggu persetujuan Wakil Rektor III.

Menunggu Rektor

Menunggu persetujuan akhir.

Direvisi

Pengajuan membutuhkan perbaikan.

Disetujui

Pengajuan diterima.

Ditolak

Pengajuan ditolak.

Selesai

Seluruh proses telah selesai.

---

# Modul Sistem

## Dashboard

Menampilkan:

- Total pengajuan
- Total pengajuan disetujui
- Total pengajuan direvisi
- Total pengajuan ditolak
- Grafik pengajuan

---

## Master Data

Tabel:

- Fakultas
- Program Studi
- Ormawa
- Dosen Pembina
- Pengguna
- Jabatan
- Tahun Akademik
- Kategori Kegiatan

---

## Pengajuan Kegiatan

Field:

- Nomor pengajuan
- Nama kegiatan
- Jenis kegiatan
- Tanggal kegiatan
- Lokasi kegiatan
- Deskripsi kegiatan
- Surat permohonan
- Proposal
- RAB

---

## Workflow Approval

Fitur:

- Approve
- Reject
- Revisi
- Catatan

---

## Riwayat Persetujuan

Menampilkan:

- Aktor
- Tanggal
- Status
- Catatan

---

## Notifikasi

Mengirim notifikasi ketika:

- Pengajuan dibuat
- Pengajuan disetujui
- Pengajuan ditolak
- Pengajuan direvisi
- Pengajuan diteruskan

---

# Rancangan Database

users

- id
- name
- email
- password
- role_id

roles

- id
- nama

fakultas

- id
- nama

ormawas

- id
- nama
- jenis
- fakultas_id
- dosen_pembina_id

pengajuans

- id
- nomor_pengajuan
- ormawa_id
- nama_kegiatan
- deskripsi
- tanggal_kegiatan
- lokasi
- status

dokumen_pengajuans

- id
- pengajuan_id
- surat_permohonan
- proposal
- rab

approvals

- id
- pengajuan_id
- approver_id
- urutan
- status
- catatan
- approved_at

notifikasis

- id
- user_id
- judul
- pesan
- dibaca

---

# Teknologi

Backend:

Laravel 12

Frontend:

Blade + Tailwind CSS

Database:

MySQL

Authentication:

Laravel Breeze

Storage:

Laravel Storage

Notification:

Laravel Notification

PDF:

DomPDF

---

# Target Pengembangan

Tahap 1

Autentikasi dan Role Management

Tahap 2

Master Data

Tahap 3

Pengajuan Kegiatan

Tahap 4

Dynamic Workflow Approval

Tahap 5

Notifikasi

Tahap 6

Laporan dan Dashboard

Tahap 7

Export PDF

---

# Catatan Penelitian

Keunggulan sistem terletak pada implementasi Dynamic Approval Workflow, yaitu mekanisme persetujuan yang menyesuaikan struktur organisasi mahasiswa secara otomatis berdasarkan jenis Ormawa yang mengajukan kegiatan.
