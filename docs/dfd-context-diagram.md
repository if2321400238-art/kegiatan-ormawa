# DFD Context Diagram

## Sistem Pengelolaan Kegiatan Ormawa UNUJA

Context diagram berikut menggambarkan sistem sebagai satu proses tunggal. Diagram hanya menampilkan entitas eksternal dan arus data yang melintasi batas sistem; data store internal belum ditampilkan pada level ini.

```mermaid
flowchart LR
    subgraph PENGGUNA[" "]
        direction TB
        M[Mahasiswa / Pengurus Ormawa]
        K[Kaprodi]
        D[Dekan]
        B[BAUAK]
        A[Administrator]
    end

    S(["<b>0</b><br/><br/>Sistem Pengelolaan<br/>Kegiatan Ormawa"])

    subgraph MITRA[" "]
        direction TB
        W[Wakil Rektor III]
        R[Rektor]
        PP[Kepala / Wakil PP]
        API[API Mahasiswa UNUJA]
        N[Layanan Notifikasi<br/>Telegram dan Email]
    end

    M -->|profil, proposal, RAB,<br/>pengajuan, dokumen, LPJ| S
    S -->|keanggotaan, status,<br/>catatan dan hasil keputusan| M
    S -->|pengajuan tingkat prodi| K
    K -->|keputusan dan catatan| S
    S -->|pengajuan lingkup fakultas| D
    D -->|keputusan dan catatan| S
    S -->|pengajuan, dokumen,<br/>LPJ dan laporan| B
    B -->|hasil verifikasi,<br/>keputusan LPJ, filter laporan| S
    A -->|master data dan<br/>parameter laporan| S
    S -->|dashboard, master data,<br/>status dan laporan| A

    S -->|pengajuan terverifikasi<br/>dan data monitoring| W
    W -->|keputusan dan catatan| S
    S -->|pengajuan disetujui Warek III| R
    R -->|keputusan dan catatan| S
    S -->|pengajuan disetujui Rektor| PP
    PP -->|keputusan akhir dan catatan| S
    S -->|autentikasi dan<br/>kriteria pencarian| API
    API -->|identitas dan data<br/>akademik mahasiswa| S
    S -->|pesan dan tujuan penerima| N
    N -->|status pengiriman dan<br/>data koneksi Telegram| S

    classDef entitas fill:#ffffff,stroke:#0000ff,stroke-width:2px,color:#000000;
    classDef proses fill:#ffffff,stroke:#0000ff,stroke-width:3px,color:#000000;
    class M,K,D,B,A,W,R,PP,API,N entitas;
    class S proses;
    style PENGGUNA fill:none,stroke:none
    style MITRA fill:none,stroke:none
    linkStyle default stroke:#0000ff,stroke-width:2px,color:#000000;
```

Versi yang dapat diatur bebas di diagrams.net tersedia pada [dfd-context-diagram.drawio](./dfd-context-diagram.drawio).

## Ringkasan arus data

| Entitas eksternal | Data masuk ke sistem | Data keluar dari sistem |
|---|---|---|
| Mahasiswa / Pengurus Ormawa | Profil, pilihan Ormawa aktif, proposal, RAB, pengajuan, dokumen pendukung, LPJ | Keanggotaan, status proses, catatan revisi/penolakan, hasil persetujuan, status LPJ |
| Kaprodi | Keputusan dan catatan persetujuan tingkat prodi | Pengajuan Ormawa tingkat prodi |
| Dekan | Keputusan dan catatan persetujuan tingkat fakultas | Pengajuan dalam lingkup fakultas |
| BAUAK | Hasil verifikasi administrasi, keputusan LPJ, parameter laporan | Pengajuan dan kelengkapannya, LPJ, laporan |
| Wakil Rektor III | Keputusan dan catatan persetujuan | Pengajuan yang lolos verifikasi BAUAK, data monitoring |
| Rektor | Keputusan dan catatan persetujuan | Pengajuan yang disetujui Wakil Rektor III |
| Kepala / Wakil PP | Keputusan akhir dan catatan | Pengajuan yang disetujui Rektor |
| Administrator | Pengelolaan master data dan parameter laporan | Dashboard, master data, status pengajuan, laporan |
| API Mahasiswa UNUJA | Data identitas dan akademik mahasiswa | Kredensial autentikasi dan kriteria pencarian |
| Layanan Notifikasi | Status pengiriman dan data koneksi Telegram | Pesan notifikasi dan tujuan penerima |

## Batasan diagram

- Seluruh modul internal—autentikasi, pengajuan, proposal/RAB, persetujuan, LPJ, notifikasi dalam aplikasi, dan laporan—digabung menjadi proses **0**.
- Database tidak ditampilkan karena data store baru diuraikan pada DFD Level 1.
- Aktor persetujuan dipisahkan agar kewenangan dan arus keputusan berjenjang tetap terlihat.
- Dosen pembina tidak dicantumkan sebagai entitas tersendiri karena implementasi aktif menggunakan Kaprodi sebagai tahap awal untuk Ormawa tingkat program studi; peran pengguna yang tersedia di aplikasi juga tidak memuat role dosen pembina.
