from html import escape
from pathlib import Path


OUTPUT = Path("docs/kamus-data-tabel-ormawa.html")


TABLES = [
    ("User", [
        ("id", "bigint unsigned", "Primary key"),
        ("fakultas_id", "bigint unsigned", "Foreign key"),
        ("prodi_id", "bigint unsigned", "Foreign key"),
        ("username", "varchar(255)", ""),
        ("nim", "varchar(255)", ""),
        ("nidn", "varchar(255)", ""),
        ("email", "varchar(255)", ""),
        ("email_verified_at", "timestamp", ""),
        ("password", "varchar(255)", ""),
        ("must_change_password", "tinyint(1)", ""),
        ("role", "enum", ""),
        ("program_studi", "varchar(255)", ""),
        ("jabatan_fungsional", "varchar(255)", ""),
        ("nama", "varchar(255)", ""),
        ("no_hp", "varchar(255)", ""),
        ("telegram_id", "varchar(255)", ""),
        ("is_active", "tinyint(1)", ""),
        ("remember_token", "varchar(100)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
        ("deleted_at", "timestamp", ""),
    ]),
    ("Fakultas", [
        ("id", "bigint unsigned", "Primary key"),
        ("dekan_user_id", "bigint unsigned", "Foreign key"),
        ("nama", "varchar(255)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("ProgramStudi", [
        ("id", "bigint unsigned", "Primary key"),
        ("fakultas_id", "bigint unsigned", "Foreign key"),
        ("kaprodi_user_id", "bigint unsigned", "Foreign key"),
        ("nama", "varchar(255)", ""),
        ("kode", "varchar(255)", ""),
        ("profile_url", "varchar(255)", ""),
        ("is_lainnya", "tinyint(1)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("Ormawa", [
        ("id", "bigint unsigned", "Primary key"),
        ("user_id", "bigint unsigned", "Foreign key"),
        ("fakultas_id", "bigint unsigned", "Foreign key"),
        ("prodi_id", "bigint unsigned", "Foreign key"),
        ("nama_ormawa", "varchar(255)", ""),
        ("ketua", "varchar(255)", ""),
        ("periode", "varchar(255)", ""),
        ("kategori_organisasi", "enum", ""),
        ("tingkat_organisasi", "enum", ""),
        ("program_studi", "varchar(255)", ""),
        ("kop_surat", "varchar(255)", ""),
        ("kontak", "varchar(255)", ""),
        ("deskripsi", "text", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
        ("deleted_at", "timestamp", ""),
    ]),
    ("AnggotaOrmawa", [
        ("id", "bigint unsigned", "Primary key"),
        ("ormawa_id", "bigint unsigned", "Foreign key"),
        ("user_id", "bigint unsigned", "Foreign key"),
        ("jabatan", "varchar(255)", ""),
        ("status", "tinyint(1)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("PengajuanKegiatan", [
        ("id", "bigint unsigned", "Primary key"),
        ("ormawa_id", "bigint unsigned", "Foreign key"),
        ("created_by_user_id", "bigint unsigned", "Foreign key"),
        ("updated_by_user_id", "bigint unsigned", "Foreign key"),
        ("judul_kegiatan", "varchar(255)", ""),
        ("tujuan_kegiatan", "text", ""),
        ("lokasi_kegiatan", "varchar(255)", ""),
        ("tempat_pesantren", "varchar(255)", ""),
        ("tanggal_mulai", "date", ""),
        ("tanggal_selesai", "date", ""),
        ("ketua_pelaksana", "varchar(255)", ""),
        ("nama_pemohon", "varchar(255)", ""),
        ("status", "enum", ""),
        ("catatan", "text", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
        ("deleted_at", "timestamp", ""),
    ]),
    ("Proposal", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("file_proposal", "varchar(255)", ""),
        ("status", "enum", ""),
        ("versi", "int", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("Rab", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("file_rab", "varchar(255)", ""),
        ("total_anggaran", "decimal(15,2)", ""),
        ("status", "enum", ""),
        ("versi", "int", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("PersetujuanKaprodi", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("user_kaprodi_id", "bigint unsigned", "Foreign key"),
        ("catatan", "text", ""),
        ("status", "varchar(255)", ""),
        ("tanggal_acc", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("PersetujuanDekan", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("user_dekan_id", "bigint unsigned", "Foreign key"),
        ("catatan", "text", ""),
        ("status", "enum", ""),
        ("tanggal_acc", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("VerifikasiBauak", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("user_bauak_id", "bigint unsigned", "Foreign key"),
        ("catatan", "text", ""),
        ("status", "enum", ""),
        ("tanggal_verifikasi", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("PersetujuanWarek3", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("user_warek3_id", "bigint unsigned", "Foreign key"),
        ("catatan", "text", ""),
        ("status", "enum", ""),
        ("tanggal_acc", "timestamp", ""),
        ("signature_path", "varchar(255)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("PersetujuanRektor", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("user_rektor_id", "bigint unsigned", "Foreign key"),
        ("catatan", "text", ""),
        ("status", "enum", ""),
        ("tanggal_acc", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("PersetujuanPp", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("user_pp_id", "bigint unsigned", "Foreign key"),
        ("catatan", "text", ""),
        ("status", "enum", ""),
        ("tanggal_acc", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("LaporanPertanggungjawaban", [
        ("id", "bigint unsigned", "Primary key"),
        ("pengajuan_id", "bigint unsigned", "Foreign key"),
        ("created_by", "bigint unsigned", "Foreign key"),
        ("verified_by", "bigint unsigned", "Foreign key"),
        ("ringkasan_pelaksanaan", "text", ""),
        ("hasil_kegiatan", "text", ""),
        ("kendala", "text", ""),
        ("tanggal_pelaksanaan_mulai", "date", ""),
        ("tanggal_pelaksanaan_selesai", "date", ""),
        ("jumlah_peserta", "int unsigned", ""),
        ("realisasi_anggaran", "decimal(15,2)", ""),
        ("sisa_anggaran", "decimal(15,2)", ""),
        ("file_laporan", "varchar(255)", ""),
        ("status", "enum", ""),
        ("catatan_verifikator", "text", ""),
        ("submitted_at", "timestamp", ""),
        ("verified_at", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("LpjRealisasiAnggaran", [
        ("id", "bigint unsigned", "Primary key"),
        ("lpj_id", "bigint unsigned", "Foreign key"),
        ("uraian", "varchar(255)", ""),
        ("anggaran_rencana", "decimal(15,2)", ""),
        ("anggaran_realisasi", "decimal(15,2)", ""),
        ("keterangan", "text", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("LpjLampiran", [
        ("id", "bigint unsigned", "Primary key"),
        ("lpj_id", "bigint unsigned", "Foreign key"),
        ("jenis", "enum", ""),
        ("nama_file", "varchar(255)", ""),
        ("file_path", "varchar(255)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("LpjVersiDokumen", [
        ("id", "bigint unsigned", "Primary key"),
        ("lpj_id", "bigint unsigned", "Foreign key"),
        ("uploaded_by", "bigint unsigned", "Foreign key"),
        ("versi", "int unsigned", ""),
        ("nama_file", "varchar(255)", ""),
        ("file_path", "varchar(255)", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("VerifikasiLpj", [
        ("id", "bigint unsigned", "Primary key"),
        ("lpj_id", "bigint unsigned", "Foreign key"),
        ("user_bauak_id", "bigint unsigned", "Foreign key"),
        ("status", "enum", ""),
        ("catatan", "text", ""),
        ("tanggal_verifikasi", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("Notifikasi", [
        ("id", "bigint unsigned", "Primary key"),
        ("user_id", "bigint unsigned", "Foreign key"),
        ("telegram_id", "varchar(255)", ""),
        ("judul", "varchar(255)", ""),
        ("pesan", "text", ""),
        ("delivery_channels", "json", ""),
        ("delivery_status", "varchar(255)", ""),
        ("link", "varchar(255)", ""),
        ("tipe", "enum", ""),
        ("dibaca", "tinyint(1)", ""),
        ("dibaca_pada", "timestamp", ""),
        ("read_at", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("TelegramConnectionCode", [
        ("id", "bigint unsigned", "Primary key"),
        ("user_id", "bigint unsigned", "Foreign key"),
        ("code_hash", "varchar(255)", ""),
        ("code_digest", "char(64)", ""),
        ("attempts", "tinyint unsigned", ""),
        ("expires_at", "timestamp", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
    ("LogAktivitas", [
        ("id", "bigint unsigned", "Primary key"),
        ("user_id", "bigint unsigned", "Foreign key"),
        ("aktivitas", "varchar(255)", ""),
        ("modul", "varchar(255)", ""),
        ("subjek_type", "varchar(255)", ""),
        ("subjek_id", "bigint unsigned", ""),
        ("deskripsi", "text", ""),
        ("ip_address", "varchar(45)", ""),
        ("user_agent", "text", ""),
        ("created_at", "timestamp", ""),
        ("updated_at", "timestamp", ""),
    ]),
]


def render_table(number, name, rows):
    title = f"Tabel 4.{number} Tabel {name}"
    body = [
        f"<h2><strong>{escape(title)}</strong></h2>",
        "<table>",
        "<thead><tr><th>No</th><th>Nama</th><th>Jenis</th><th>Key</th></tr></thead>",
        "<tbody>",
    ]
    for index, (field, kind, key) in enumerate(rows, start=1):
        body.append(
            "<tr>"
            f"<td>{index}</td>"
            f"<td>{escape(field)}</td>"
            f"<td>{escape(kind)}</td>"
            f"<td>{escape(key)}</td>"
            "</tr>"
        )
    body.extend(["</tbody>", "</table>"])
    return "\n".join(body)


def main():
    sections = "\n".join(
        render_table(index, name, rows)
        for index, (name, rows) in enumerate(TABLES, start=1)
    )
    html = f"""<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kamus Data Sistem Pengelolaan Kegiatan Ormawa</title>
  <style>
    @page {{
      size: A4;
      margin: 2cm;
    }}
    body {{
      font-family: "Times New Roman", serif;
      font-size: 12pt;
      color: #000;
      line-height: 1.25;
    }}
    h1 {{
      font-size: 16pt;
      text-align: center;
      margin: 0 0 18pt;
    }}
    h2 {{
      font-size: 14pt;
      margin: 18pt 0 6pt;
      page-break-after: avoid;
    }}
    table {{
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14pt;
      page-break-inside: avoid;
    }}
    th, td {{
      border: 1px solid #000;
      padding: 4pt 6pt;
      vertical-align: top;
    }}
    th {{
      font-weight: bold;
      text-align: left;
    }}
    th:first-child, td:first-child {{
      width: 8%;
      text-align: center;
    }}
    th:nth-child(2), td:nth-child(2) {{
      width: 42%;
    }}
    th:nth-child(3), td:nth-child(3) {{
      width: 25%;
    }}
    th:nth-child(4), td:nth-child(4) {{
      width: 25%;
    }}
  </style>
</head>
<body>
  <h1>Kamus Data Sistem Pengelolaan Kegiatan Ormawa UNUJA</h1>
  {sections}
</body>
</html>
"""
    OUTPUT.write_text(html, encoding="utf-8")


if __name__ == "__main__":
    main()
