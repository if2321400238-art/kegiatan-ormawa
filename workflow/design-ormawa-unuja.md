# Design System: Sistem Ormawa — Gaya Visual SSO UNUJA

> Dokumen ini mendefinisikan token desain, komponen, dan pola tata letak untuk
> meredesain aplikasi **Pengajuan Kegiatan Ormawa** agar selaras secara visual
> dengan Portal SSO Universitas Nurul Jadid.

---

## 1. Referensi Visual

| Elemen | Portal SSO UNUJA (Referensi) | Sistem Ormawa (Saat Ini) |
|---|---|---|
| Warna utama | Biru tua `#1E3A6E` + ungu `#6C63FF` | Abu-abu netral |
| Header navigasi | Putih, shadow tipis, pill aktif | Border-bottom sederhana |
| Kartu statistik | Rounded-xl, border kiri berwarna, ikon kotak | Kartu datar, border penuh |
| Avatar pengguna | Kotak inisial besar, warna latar biru tua | Lingkaran kecil |
| Badge status | Pill kecil berwarna: hijau/biru/merah | Teks warna saja |
| Tombol aksi | Outlined pill + ikon Tabler | Teks biasa |
| Tipografi | Inter/system-ui, heading bold `700`, subtext ringan | Default browser |

---

## 2. Token Warna

```css
/* === Brand Utama === */
--color-brand-primary:   #1E3A6E;   /* Biru tua UNUJA — header, sidebar aktif */
--color-brand-accent:    #6C63FF;   /* Ungu aksen — badge, highlight, link */
--color-brand-surface:   #EEF2FF;   /* Ungu sangat muda — latar kartu aksen */

/* === Status Semantik === */
--color-success:         #10B981;   /* Hijau — Disetujui */
--color-success-light:   #D1FAE5;   /* Latar badge disetujui */
--color-warning:         #F59E0B;   /* Kuning — Menunggu / Revisi */
--color-warning-light:   #FEF3C7;   /* Latar badge menunggu */
--color-danger:          #EF4444;   /* Merah — Ditolak */
--color-danger-light:    #FEE2E2;   /* Latar badge ditolak */
--color-info:            #3B82F6;   /* Biru — Informasi umum */
--color-info-light:      #DBEAFE;   /* Latar badge info */

/* === Netral === */
--color-gray-50:         #F9FAFB;
--color-gray-100:        #F3F4F6;
--color-gray-200:        #E5E7EB;
--color-gray-400:        #9CA3AF;
--color-gray-600:        #4B5563;
--color-gray-900:        #111827;

/* === Background & Surface === */
--bg-page:               #F4F6FB;   /* Latar halaman — lebih hangat dari abu */
--bg-card:               #FFFFFF;
--bg-sidebar:            #1E3A6E;   /* Sidebar gelap seperti SSO */
--bg-sidebar-active:     #2D5196;   /* Item aktif sidebar */
```

---

## 3. Tipografi

```css
/* Font utama: Inter (Google Fonts) atau fallback system-ui */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

--font-family: 'Inter', system-ui, -apple-system, sans-serif;

/* Skala tipografi */
--text-xs:    11px;   /* Keterangan mikro, badge */
--text-sm:    13px;   /* Label kartu, subtext */
--text-base:  15px;   /* Teks isi utama */
--text-lg:    18px;   /* Judul bagian */
--text-xl:    24px;   /* Angka statistik */
--text-2xl:   30px;   /* Angka besar (jam, total utama) */

--weight-normal:   400;
--weight-medium:   500;
--weight-semibold: 600;
--weight-bold:     700;

--leading-tight:   1.25;
--leading-normal:  1.6;
```

---

## 4. Spacing & Radius

```css
/* Spacing 4px base */
--space-1:   4px;
--space-2:   8px;
--space-3:  12px;
--space-4:  16px;
--space-5:  20px;
--space-6:  24px;
--space-8:  32px;
--space-10: 40px;

/* Radius */
--radius-sm:   6px;    /* Badge, input, pill kecil */
--radius-md:   10px;   /* Tombol, kartu kecil */
--radius-lg:   14px;   /* Kartu utama */
--radius-xl:   20px;   /* Modal, panel besar */
--radius-full: 9999px; /* Badge pill, avatar bulat */

/* Shadow */
--shadow-xs: 0 1px 2px rgba(0,0,0,.05);
--shadow-sm: 0 2px 8px rgba(0,0,0,.06);
--shadow-md: 0 4px 16px rgba(0,0,0,.08);
```

---

## 5. Tata Letak (Layout)

### 5.1 Shell Halaman

```
┌─────────────────────────────────────────────────────┐
│  SIDEBAR (260px, bg biru tua)                        │
│  ┌───────────────────────────────────────────────┐  │
│  │ Logo + Nama Sistem                            │  │
│  ├───────────────────────────────────────────────┤  │
│  │ 📊 Dashboard          ← item aktif (pill biru)│  │
│  │ 🏛  Kelola Ormawa                             │  │
│  │ 📋 Pengajuan                                  │  │
│  │ ⚙️  Pengaturan                                │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  KONTEN UTAMA (flex-1, bg-page #F4F6FB)             │
│  ┌─ TOPBAR (sticky, putih, shadow) ──────────────┐  │
│  │  Judul Halaman           🔔 Avatar (AS)       │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  AREA KONTEN (padding 24px)                         │
│  [Kartu statistik 4 kolom]                         │
│  [Tabel / List / Detail]                           │
└─────────────────────────────────────────────────────┘
```

### 5.2 Grid Statistik

```
┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ ▐ Total      │  │ ▐ Menunggu   │  │ ▐ Ditolak    │  │ ▐ Perlu      │
│   Ormawa     │  │   Persetujuan│  │              │  │   Revisi     │
│   5          │  │   0          │  │   0          │  │   0          │
│ border-biru  │  │ border-oren  │  │ border-merah │  │ border-kuning│
└──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘
```

---

## 6. Komponen

### 6.1 Sidebar Item

```html
<!-- Item Aktif -->
<a class="nav-item active">
  <div class="nav-icon">
    <i class="ti ti-layout-dashboard"></i>
  </div>
  <span>Dashboard</span>
</a>
```

```css
.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  border-radius: var(--radius-md);
  color: rgba(255,255,255,0.7);
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  transition: background 0.15s;
  cursor: pointer;
}
.nav-item:hover   { background: rgba(255,255,255,0.08); color: #fff; }
.nav-item.active  { background: var(--bg-sidebar-active); color: #fff; }
.nav-icon {
  width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius-sm);
  background: rgba(255,255,255,0.12);
  font-size: 16px;
}
```

---

### 6.2 Kartu Statistik (Mirip SSO UNUJA)

Setiap kartu memiliki **border kiri berwarna** dan **ikon kotak** — pola khas
dari Portal SSO UNUJA.

```html
<div class="stat-card" style="--accent: #3B82F6">
  <div class="stat-icon" style="background: #DBEAFE">
    <i class="ti ti-building-community" style="color:#3B82F6"></i>
  </div>
  <div class="stat-info">
    <span class="stat-label">Total Ormawa</span>
    <span class="stat-value">5</span>
  </div>
</div>
```

```css
.stat-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 0.5px solid var(--color-gray-200);
  border-left: 4px solid var(--accent);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow-sm);
}
.stat-icon {
  width: 48px; height: 48px;
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}
.stat-label {
  font-size: var(--text-sm);
  color: var(--color-gray-400);
  font-weight: var(--weight-medium);
  display: block;
}
.stat-value {
  font-size: var(--text-xl);
  font-weight: var(--weight-bold);
  color: var(--color-gray-900);
  line-height: 1.2;
  display: block;
}
```

**Palet aksen per kartu:**

| Kartu | `--accent` | Icon bg | Icon color |
|---|---|---|---|
| Total Ormawa | `#3B82F6` | `#DBEAFE` | `#3B82F6` |
| Total Pengajuan | `#10B981` | `#D1FAE5` | `#10B981` |
| Menunggu Persetujuan | `#F59E0B` | `#FEF3C7` | `#F59E0B` |
| Pengajuan Ditolak | `#EF4444` | `#FEE2E2` | `#EF4444` |
| Perlu Revisi | `#F97316` | `#FFEDD5` | `#F97316` |

---

### 6.3 Badge Status

```html
<span class="badge badge-success">Disetujui</span>
<span class="badge badge-warning">Menunggu</span>
<span class="badge badge-danger">Ditolak</span>
<span class="badge badge-info">Revisi</span>
```

```css
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: var(--weight-semibold);
  letter-spacing: 0.02em;
}
.badge::before {
  content: '';
  width: 6px; height: 6px;
  border-radius: 50%;
  background: currentColor;
}
.badge-success { background: var(--color-success-light); color: #047857; }
.badge-warning { background: var(--color-warning-light); color: #92400E; }
.badge-danger  { background: var(--color-danger-light);  color: #991B1B; }
.badge-info    { background: var(--color-info-light);    color: #1D4ED8; }
```

---

### 6.4 Kartu Aksi Cepat (Mirip Grid Aplikasi SSO)

Mengganti tombol "Ormawa" dan "Pengajuan" lama dengan kartu bergaya SSO:

```html
<div class="quick-action-card" onclick="navigate('/ormawa')">
  <div class="qa-icon" style="background:#1E3A6E">
    <i class="ti ti-building-community"></i>
  </div>
  <span class="qa-arrow"><i class="ti ti-arrow-right"></i></span>
  <p class="qa-title">Ormawa</p>
  <p class="qa-desc">Kelola ormawa</p>
</div>
```

```css
.quick-action-card {
  background: var(--bg-card);
  border: 0.5px solid var(--color-gray-200);
  border-radius: var(--radius-lg);
  padding: 20px;
  cursor: pointer;
  position: relative;
  transition: box-shadow 0.15s, transform 0.15s;
}
.quick-action-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}
.qa-icon {
  width: 44px; height: 44px;
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 20px;
  margin-bottom: 12px;
}
.qa-arrow {
  position: absolute;
  top: 16px; right: 16px;
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--color-info-light);
  color: var(--color-info);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
}
.qa-title { font-size: var(--text-base); font-weight: var(--weight-semibold); margin: 0 0 2px; }
.qa-desc  { font-size: var(--text-sm); color: var(--color-gray-400); margin: 0; }
```

---

### 6.5 Tabel Pengajuan Terbaru

```css
.table-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 0.5px solid var(--color-gray-200);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.table-card table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}
.table-card thead tr {
  background: var(--color-gray-50);
  border-bottom: 1px solid var(--color-gray-200);
}
.table-card th {
  padding: 12px 16px;
  text-align: left;
  font-weight: var(--weight-semibold);
  color: var(--color-gray-600);
  font-size: var(--text-xs);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.table-card td {
  padding: 14px 16px;
  color: var(--color-gray-900);
  border-bottom: 0.5px solid var(--color-gray-100);
}
.table-card tr:last-child td { border-bottom: none; }
.table-card tr:hover td     { background: var(--color-gray-50); }
```

---

### 6.6 Daftar Ormawa Paling Aktif

Mengganti nomor bulat biru dengan gaya **ranking pill** yang konsisten:

```html
<div class="active-org-row">
  <span class="rank-pill">1</span>
  <span class="org-name">Badan Eksekutif Mahasiswa</span>
  <span class="badge badge-info">0 pengajuan</span>
</div>
```

```css
.active-org-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 0.5px solid var(--color-gray-100);
}
.active-org-row:last-child { border-bottom: none; }
.rank-pill {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--color-brand-primary);
  color: #fff;
  font-size: var(--text-xs);
  font-weight: var(--weight-bold);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.org-name {
  flex: 1;
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  color: var(--color-gray-900);
}
```

---

### 6.7 Topbar

```html
<header class="topbar">
  <div class="topbar-title">
    <h1>Dashboard Admin</h1>
    <span class="topbar-breadcrumb">Sistem Ormawa / Dashboard</span>
  </div>
  <div class="topbar-actions">
    <button class="icon-btn" aria-label="Notifikasi">
      <i class="ti ti-bell"></i>
    </button>
    <div class="avatar-pill">AS</div>
  </div>
</header>
```

```css
.topbar {
  position: sticky; top: 0; z-index: 10;
  background: var(--bg-card);
  border-bottom: 1px solid var(--color-gray-200);
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: var(--shadow-xs);
}
.topbar h1 {
  font-size: var(--text-lg);
  font-weight: var(--weight-bold);
  color: var(--color-gray-900);
  margin: 0;
}
.topbar-breadcrumb {
  font-size: var(--text-xs);
  color: var(--color-gray-400);
  display: block;
}
.topbar-actions { display: flex; align-items: center; gap: 8px; }
.icon-btn {
  width: 36px; height: 36px;
  border-radius: var(--radius-md);
  border: 0.5px solid var(--color-gray-200);
  background: transparent;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 18px; color: var(--color-gray-600);
}
.icon-btn:hover { background: var(--color-gray-100); }
.avatar-pill {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: var(--color-brand-accent);
  color: #fff;
  font-size: var(--text-sm);
  font-weight: var(--weight-bold);
  display: flex; align-items: center; justify-content: center;
}
```

---

## 7. Perbedaan Desain: Sebelum vs Sesudah

| Aspek | Sebelum (Ormawa Lama) | Sesudah (Gaya SSO UNUJA) |
|---|---|---|
| **Background halaman** | Putih penuh (#fff) | Abu biru muda (#F4F6FB) |
| **Sidebar** | Tidak ada | Biru tua (#1E3A6E), item pill |
| **Header/Topbar** | Border-bottom sederhana | Shadow tipis, breadcrumb, aksi kanan |
| **Kartu statistik** | Kartu datar, border penuh | Border kiri berwarna, ikon kotak |
| **Jumlah kartu** | 5 kartu (2 besar + 3 kecil) | 4–5 kartu seragam dalam 1 grid |
| **Grid aksi cepat** | 2 kartu (Ormawa, Pengajuan) | Grid ikon berlogo, panah lingkaran |
| **Badge status** | Teks warna saja | Pill berwarna + titik indikator |
| **Tabel** | Minimalis | Header abu, hover-row, border tipis |
| **Tipografi** | Default browser | Inter, skala ketat, weight terdefinisi |
| **Warna aksen** | Biru generik | Ungu brand `#6C63FF` + biru tua |

---

## 8. Panduan Implementasi

1. **Import font** Inter dari Google Fonts di `<head>`.
2. **Definisikan semua token CSS** di `:root` sebelum menggunakan komponen.
3. **Sidebar** diimplementasikan sebagai `position: fixed` kiri, lebar 260px; konten utama mendapat `margin-left: 260px`.
4. **Grid statistik** menggunakan `display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;`.
5. **Grid aksi cepat** menggunakan grid yang sama dengan `minmax(160px, 1fr)`.
6. **Semua badge status** menggunakan kelas `.badge` yang dikomposisi — tidak ada warna hard-coded di template.
7. **Tabel** dibungkus `.table-card` untuk mendapat sudut bulat + border yang konsisten.
8. **Ikon** menggunakan Tabler Icons outline (`ti ti-*`), bukan emoji atau SVG manual.

---

*Dokumen ini dibuat untuk menyeragamkan pengalaman visual Sistem Ormawa
dengan ekosistem Portal SSO Universitas Nurul Jadid. Semua token dapat
dioverride melalui CSS custom properties untuk kebutuhan tema atau
penyesuaian di masa mendatang.*
