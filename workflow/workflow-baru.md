# WORKFLOW SISTEM PENGELOLAAN KEGIATAN ORMAWA

## Gambaran Umum

Sistem menggunakan pendekatan **user-centric**, yaitu setiap pengguna yang login adalah individu (mahasiswa, dosen, dekan, admin, dan lainnya), bukan organisasi.

Mahasiswa dapat menjadi anggota lebih dari satu organisasi mahasiswa (Ormawa), baik organisasi internal maupun eksternal.

---

# Aktor Sistem

## 1. Mahasiswa

Mahasiswa login menggunakan:

* NIM
* Password

Mahasiswa dapat:

* Menjadi anggota satu atau lebih Ormawa
* Membuat pengajuan kegiatan atas nama Ormawa yang diikutinya
* Melihat status pengajuan
* Mengunggah dokumen pendukung

---

## 2. Dosen Pembina

Dosen Pembina bertugas:

* Memverifikasi pengajuan kegiatan dari Ormawa yang dibinanya
* Memberikan catatan revisi
* Menyetujui atau menolak pengajuan

---

## 3. Dekan

Dekan hanya dapat:

* Melihat pengajuan dari Ormawa yang berada di fakultasnya
* Menyetujui atau menolak pengajuan tingkat fakultas

Contoh:

* Dekan Fakultas Teknik hanya melihat pengajuan Ormawa Fakultas Teknik
* Dekan Fakultas Agama Islam hanya melihat pengajuan Ormawa Fakultas Agama Islam

---

## 4. BAUAK

BAUAK bertugas:

* Memverifikasi administrasi
* Memastikan kelengkapan dokumen
* Meneruskan pengajuan ke Warek III

---

## 5. Wakil Rektor III

Warek III bertugas:

* Memeriksa dan memberikan persetujuan tingkat Wakil Rektor III
* Meneruskan pengajuan ke Rektor

---

## 6. Rektor

Rektor bertugas:

* Memberikan persetujuan tingkat Rektor
* Meneruskan pengajuan ke Kepala/Wakil PP

---

## 7. Kepala/Wakil PP

Kepala/Wakil Pimpinan Pondok bertugas:

* Melakukan monitoring seluruh pengajuan
* Memberikan persetujuan akhir setelah persetujuan Rektor
* Menyetujui atau menolak pengajuan dengan catatan keputusan

---

## 8. Administrator

Administrator bertugas:

* Mengelola pengguna
* Mengelola data Ormawa
* Mengelola Fakultas
* Mengelola Dosen Pembina
* Mengelola data pendukung sistem

---

# Struktur Organisasi

## Fakultas

Setiap Ormawa internal tingkat fakultas wajib memiliki relasi ke Fakultas.

Contoh:

* Fakultas Teknik
* Fakultas Agama Islam
* Fakultas Kesehatan
* Fakultas Sosial dan Humaniora
* Program Pascasarjana

---

## Ormawa

Ormawa terdiri dari:

### Internal Universitas

Contoh:

* BEM Universitas
* DPM Universitas
* UKM

### Internal Fakultas

Contoh:

* BEM Fakultas Teknik
* BEM Fakultas Agama Islam

### Eksternal

Contoh:

* HMI
* PMII
* IMM
* GMNI
* KAMMI

---

# Relasi Data

## User

Semua pengguna disimpan pada tabel:

users

Role:

* admin
* mahasiswa
* dosen
* dekan
* bauak
* warek3

---

## Fakultas

fakultas

* id
* nama
* dekan_user_id

---

## Ormawa

ormawa

* id
* nama_ormawa
* kategori_organisasi
* tingkat_organisasi
* fakultas_id
* pembina_user_id

---

## Keanggotaan Ormawa

ormawa_users

* id
* ormawa_id
* user_id
* jabatan
* status

Contoh jabatan:

* ketua
* wakil_ketua
* sekretaris
* bendahara
* anggota

---

# Workflow Pengajuan Kegiatan

## Tahap 1 - Pengajuan oleh Mahasiswa

Mahasiswa login menggunakan NIM.

Sistem menampilkan seluruh Ormawa yang diikutinya.

Contoh:

* BEM Fakultas Teknik
* PMII Rayon Teknik
* UKM Futsal

Mahasiswa memilih salah satu Ormawa lalu membuat pengajuan kegiatan.

Sebelum membuka form pengajuan, sistem memeriksa Ormawa pengguna:

* Akun dengan role Ormawa menggunakan relasi `users.id` ke `ormawa.user_id`.
* Akun mahasiswa menggunakan keanggotaan aktif pada tabel `anggota_ormawa`.
* Jika mahasiswa mengikuti lebih dari satu Ormawa, mahasiswa harus memilih Ormawa aktif.
* Sistem menyimpan pilihan pada sesi `active_ormawa_id` dan memvalidasi ulang keanggotaannya pada setiap akses pengajuan.
* Pengguna tanpa Ormawa yang valid tidak dapat membuka atau menyimpan form pengajuan.

Status:

MENUNGGU_DOSEN

---

## Tahap 2 - Verifikasi Dosen Pembina

Dosen Pembina menerima notifikasi.

Keputusan:

### Disetujui

Jika organisasi:

* Internal Universitas

Status:

MENUNGGU_BAUAK

Jika organisasi:

* Internal Fakultas

Status:

MENUNGGU_DEKAN

### Revisi

Status:

REVISI_DOSEN

### Ditolak

Status:

DITOLAK_DOSEN

---

## Tahap 3 - Persetujuan Dekan

Khusus organisasi tingkat fakultas.

Dekan hanya dapat melihat pengajuan dari fakultasnya.

### Disetujui

Status:

MENUNGGU_BAUAK

### Revisi

Status:

REVISI_DEKAN

### Ditolak

Status:

DITOLAK_DEKAN

---

## Tahap 4 - Verifikasi BAUAK

BAUAK memeriksa:

* Proposal
* RAB
* Dokumen pendukung

### Disetujui

Status:

MENUNGGU_WAREK3

### Revisi

Status:

REVISI_BAUAK

### Ditolak

Status:

DITOLAK_BAUAK

---

## Tahap 5 - Persetujuan Warek III

Warek III melakukan verifikasi akhir.

### Disetujui

Status:

DISETUJUI

Sistem:

* Mengirim notifikasi ke pemohon

### Revisi

Status:

REVISI_WAREK3

### Ditolak

Status:

DITOLAK_WAREK3

---

# Workflow Notifikasi

## Mahasiswa Mengajukan

Notifikasi dikirim ke:

* Dosen Pembina

---

## Dosen Menyetujui

Jika tingkat universitas:

Notifikasi ke:

* BAUAK

Jika tingkat fakultas:

Notifikasi ke:

* Dekan Fakultas terkait

---

## Dekan Menyetujui

Notifikasi ke:

* BAUAK

---

## BAUAK Menyetujui

Notifikasi ke:

* Warek III

---

## Warek III Menyetujui

Notifikasi ke:

* Mahasiswa pengaju
* Pengurus Ormawa terkait

---

# Aturan Akses

## Mahasiswa

Hanya dapat mengakses:

* Ormawa yang diikutinya
* Pengajuan yang dibuatnya

---

## Dosen Pembina

Hanya dapat mengakses:

* Ormawa yang dibinanya

---

## Dekan

Hanya dapat mengakses:

* Pengajuan dari fakultasnya

---

## BAUAK

Dapat mengakses seluruh pengajuan yang sudah lolos tahap sebelumnya.

---

## Warek III

Dapat mengakses seluruh pengajuan yang telah diverifikasi BAUAK.

---

## Rektor

Dapat mengakses pengajuan yang telah disetujui Warek III.

---

## Kepala/Wakil PP

Dapat mengakses pengajuan yang telah disetujui Rektor dan memberikan keputusan akhir.

---

# Status Workflow

* draft
* menunggu_dosen
* revisi_dosen
* ditolak_dosen
* menunggu_dekan
* revisi_dekan
* ditolak_dekan
* menunggu_bauak
* revisi_bauak
* ditolak_bauak
* menunggu_warek3
* revisi_warek3
* ditolak_warek3
* disetujui
