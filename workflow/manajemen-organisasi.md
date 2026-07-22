# Manajemen Organisasi dan Keanggotaan

## Tujuan

Membangun sistem pengelolaan organisasi mahasiswa yang terintegrasi dengan pengajuan kegiatan sehingga mahasiswa, organisasi, dan pihak kampus memiliki alur kerja yang jelas.

---

# Aktor Sistem

## 1. Mahasiswa

Mahasiswa dapat:

* Login ke sistem
* Mengajukan pendaftaran organisasi baru
* Mengajukan permintaan bergabung ke organisasi
* Melihat status keanggotaan
* Mengajukan kegiatan atas nama organisasi yang diikuti

---

## 2. Ketua Organisasi

Ketua organisasi dapat:

* Mengelola data organisasi
* Menyetujui atau menolak anggota baru
* Mengelola pengurus organisasi
* Mengajukan kegiatan organisasi
* Mengelola anggota organisasi

---

## 3. BAUAK

BAUAK bertugas:

* Memverifikasi organisasi baru
* Menyetujui atau menolak organisasi yang didaftarkan
* Melihat statistik organisasi
* Melakukan monitoring aktivitas organisasi

BAUAK **tidak mengelola anggota organisasi secara langsung**.

---

# Alur Pendaftaran Organisasi Baru

## Langkah 1

Mahasiswa mengisi formulir:

* Nama Organisasi
* Jenis Organisasi
* Fakultas (opsional)
* Deskripsi Organisasi
* Logo Organisasi
* SK Organisasi (PDF)

Status awal:

```text
Pending
```

---

## Langkah 2

Data organisasi masuk ke BAUAK.

BAUAK melakukan verifikasi:

### Setujui

Status berubah menjadi:

```text
Aktif
```

### Tolak

Status berubah menjadi:

```text
Ditolak
```

dengan catatan alasan penolakan.

---

## Langkah 3

Apabila organisasi disetujui:

* Organisasi aktif
* Pembuat organisasi otomatis menjadi Ketua Organisasi

---

# Alur Penentuan Ketua Organisasi

Saat organisasi pertama kali dibuat:

```text
Mahasiswa Pembuat Organisasi
↓
Otomatis Menjadi Ketua
```

Contoh:

```text
Rizky membuat PMII Teknik
↓
Rizky menjadi Ketua PMII Teknik
```

---

# Struktur Data Keanggotaan

## Tabel organisasi

| Field       | Keterangan                |
| ----------- | ------------------------- |
| id          | Primary Key               |
| nama_ormawa | Nama organisasi           |
| jenis       | Fakultas / Universitas    |
| status      | pending / aktif / ditolak |
| deskripsi   | Deskripsi organisasi      |
| logo        | Logo organisasi           |

---

## Tabel anggota_ormawa

| Field          | Keterangan                            |
| -------------- | ------------------------------------- |
| id             | Primary Key                           |
| user_id        | Mahasiswa                             |
| ormawa_id      | Organisasi                            |
| jabatan        | Ketua, Sekretaris, Bendahara, Anggota |
| status         | aktif, nonaktif                       |
| tanggal_gabung | Tanggal bergabung                     |

---

# Alur Bergabung ke Organisasi

## Langkah 1

Mahasiswa membuka menu:

```text
Daftar Organisasi
```

---

## Langkah 2

Mahasiswa memilih organisasi:

```text
PMII
HMI
BEM
UKM Basket
UKM Robotik
```

---

## Langkah 3

Klik tombol:

```text
Ajukan Bergabung
```

Status:

```text
Pending
```

---

## Langkah 4

Ketua organisasi menerima notifikasi:

```text
Permintaan Anggota Baru
```

---

## Langkah 5

Ketua organisasi memilih:

### Terima

Status:

```text
Aktif
```

Mahasiswa resmi menjadi anggota.

### Tolak

Status:

```text
Ditolak
```

---

# Pergantian Ketua Organisasi

Ketua organisasi dapat diganti tanpa mengubah data organisasi.

Contoh:

Sebelum:

| Nama  | Jabatan     |
| ----- | ----------- |
| Rizky | Ketua       |
| Ahmad | Wakil Ketua |

Sesudah:

| Nama  | Jabatan |
| ----- | ------- |
| Rizky | Anggota |
| Ahmad | Ketua   |

Riwayat keanggotaan tetap tersimpan.

---

# Integrasi Dengan Pengajuan Kegiatan

Mahasiswa hanya dapat mengajukan kegiatan apabila:

1. Memiliki organisasi aktif
2. Berstatus anggota aktif organisasi

Saat membuat pengajuan:

```text
Mahasiswa
↓
Memilih Organisasi Aktif
↓
Membuat Pengajuan Kegiatan
```

---

# Alur Sistem Keseluruhan

```text
Mahasiswa
↓
Daftar Organisasi Baru
↓
BAUAK Verifikasi
↓
Organisasi Aktif
↓
Pembuat Menjadi Ketua
↓
Mahasiswa Lain Mengajukan Bergabung
↓
Ketua Menyetujui
↓
Menjadi Anggota
↓
Organisasi Mengajukan Kegiatan
↓
Proses Persetujuan Kegiatan
```

---

# Keuntungan Desain

* Sesuai proses organisasi kampus
* BAUAK tidak terbebani mengelola anggota
* Ketua organisasi memiliki kontrol anggota
* Mendukung pergantian pengurus
* Mudah dikembangkan menjadi sistem organisasi kampus skala besar
* Mendukung proses pengajuan kegiatan yang terintegrasi
