# Implementasi Persetujuan Dekan Berdasarkan Fakultas

## Latar Belakang

Pada sistem pengajuan kegiatan Ormawa, persetujuan Dekan harus dilakukan berdasarkan fakultas dari Ormawa yang mengajukan kegiatan.

Contoh:

* HMJ Teknik Informatika → Dekan Fakultas Teknik
* HMJ Teknologi Informasi → Dekan Fakultas Teknik
* HMJ Pendidikan Agama Islam → Dekan Fakultas Agama Islam
* BEM Fakultas Teknik → Dekan Fakultas Teknik
* BEM Fakultas Agama Islam → Dekan Fakultas Agama Islam

Sedangkan organisasi tingkat universitas atau organisasi eksternal tidak memerlukan persetujuan Dekan dan langsung menuju BAUAK.

---

# Struktur Fakultas

## Pascasarjana

Dekan:

* Dr. H. Akmal Mundiri, M.Pd.

Program Studi:

* S2 Pendidikan Agama Islam (PAI)
* S2 Manajemen Pendidikan Islam (MPI)
* S2 Studi Islam (SI)
* S3 Studi Islam (SI)

---

## Fakultas Agama Islam

Dekan:

* Dr. H. Ahmad Fawaid, M.Th.I.

Program Studi:

* S1 Komunikasi dan Penyiaran Islam (KPI)
* S1 Pendidikan Agama Islam (PAI)
* S1 Manajemen Pendidikan Islam (MPI)
* S1 Pendidikan Bahasa Arab (PBA)
* S1 Hukum Keluarga Islam (HKI/AS)
* S1 Ilmu Al-Quran dan Tafsir (IQT)
* S1 Ekonomi Syariah (ES)
* S1 Perbankan Syariah (PS)
* S1 Pendidikan Guru MI (PGMI)

---

## Fakultas Teknik

Dekan:

* Zainal Arifin, M.Kom.

Program Studi:

* S1 Teknik Informatika (IF)
* S1 Teknologi Informasi (TI)
* S1 Teknik Elektro (TE)
* S1 Teknologi Pertanian (TP)

---

## Fakultas Kesehatan

Dekan:

* Dr. Sri Astutik Andayani, S.Kep., M.Kes.

Program Studi:

* S1 Keperawatan
* D3 Kebidanan
* Profesi Ners

---

## Fakultas Sosial dan Humaniora

Dekan:

* Dr. H. Chusnul Muali, M.Pd.

Program Studi:

* S1 Pendidikan Bahasa Inggris (PBI)
* S1 Pendidikan Matematika (MAT)
* S1 Hukum
* S1 Ekonomi

---

# Perubahan Database

## Tabel fakultas

```php
Schema::create('fakultas', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->foreignId('dekan_user_id')
        ->nullable()
        ->constrained('users');
    $table->timestamps();
});
```

---

## Tambahan pada tabel ormawa

```php
Schema::table('ormawa', function (Blueprint $table) {
    $table->foreignId('fakultas_id')
        ->nullable()
        ->after('tingkat_organisasi');
});
```

---

# Relasi Model

## Fakultas

```php
public function dekan()
{
    return $this->belongsTo(User::class, 'dekan_user_id');
}

public function ormawa()
{
    return $this->hasMany(Ormawa::class);
}
```

## Ormawa

```php
public function fakultas()
{
    return $this->belongsTo(Fakultas::class);
}
```

## User (Dekan)

```php
public function fakultas()
{
    return $this->hasOne(Fakultas::class, 'dekan_user_id');
}
```

---

# Workflow Persetujuan

## Ormawa Fakultas

Alur:

```text
Ormawa
   ↓
Dosen Pembina
   ↓
Dekan Fakultas
   ↓
BAUAK
   ↓
Wakil Rektor III
   ↓
Rektor
   ↓
Disetujui
```

---

## Ormawa Universitas

Contoh:

* BEM Universitas
* UKM
* LPM
* Organisasi tingkat universitas lainnya

Alur:

```text
Ormawa
   ↓
Dosen Pembina
   ↓
BAUAK
   ↓
Wakil Rektor III
   ↓
Rektor
   ↓
Disetujui
```

---

## Organisasi Eksternal

Contoh:

* PMII
* HMI
* GMNI
* IMM
* KAMMI
* dan organisasi eksternal lainnya

Alur:

```text
Organisasi
   ↓
Dosen Pembina
   ↓
BAUAK
   ↓
Wakil Rektor III
   ↓
Rektor
   ↓
Disetujui
```

---

# Logika Setelah Verifikasi Dosen

```php
if (
    $pengajuan->ormawa->kategori_organisasi === 'internal'
    && $pengajuan->ormawa->tingkat_organisasi === 'fakultas'
) {
    $pengajuan->update([
        'status' => 'menunggu_dekan'
    ]);
} else {
    $pengajuan->update([
        'status' => 'menunggu_bauak'
    ]);
}
```

---

# Filter Persetujuan Dekan

Dekan hanya dapat melihat pengajuan dari fakultasnya sendiri.

```php
$fakultasId = auth()->user()->fakultas->id;

$pengajuan = PengajuanKegiatan::where('status', 'menunggu_dekan')
    ->whereHas('ormawa', function ($query) use ($fakultasId) {
        $query->where('fakultas_id', $fakultasId);
    })
    ->get();
```

---

# Target Implementasi

## Tahap 1

* Tabel fakultas
* Seeder fakultas
* Relasi fakultas pada Ormawa

## Tahap 2

* Assign Dekan ke Fakultas
* Filter dashboard Dekan

## Tahap 3

* Notifikasi otomatis ke Dekan sesuai fakultas
* Persetujuan Dekan berdasarkan fakultas

## Tahap 4

* Audit hak akses
* Pengujian workflow lintas fakultas
* Feature test untuk Dekan Fakultas
* Feature test untuk organisasi universitas
* Feature test untuk organisasi eksternal

```
```
