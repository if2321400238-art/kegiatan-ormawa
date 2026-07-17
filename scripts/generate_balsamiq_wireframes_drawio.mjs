import fs from 'node:fs';

const output = 'docs/prototype-wireframe-balsamiq.drawio';

const esc = (value) => String(value)
  .replaceAll('&', '&amp;')
  .replaceAll('<', '&lt;')
  .replaceAll('>', '&gt;')
  .replaceAll('"', '&quot;')
  .replaceAll("'", '&#39;')
  .replaceAll('\n', '&#xa;');

const base = 'html=1;whiteSpace=wrap;strokeColor=#222222;fillColor=#ffffff;fontColor=#111111;fontFamily=Comic Sans MS;fontSize=12;sketch=1;curveFitting=1;jiggle=2;';
const textStyle = 'text;html=1;whiteSpace=wrap;strokeColor=none;fillColor=none;fontColor=#111111;fontFamily=Comic Sans MS;fontSize=12;';
const dark = 'html=1;whiteSpace=wrap;strokeColor=#222222;fillColor=#eeeeee;fontColor=#111111;fontFamily=Comic Sans MS;fontSize=12;sketch=1;curveFitting=1;jiggle=2;';
const titleStyle = `${textStyle}fontStyle=1;fontSize=18;align=left;verticalAlign=middle;`;

function cell(id, value, x, y, w, h, style = base) {
  return `        <mxCell id="${id}" value="${esc(value)}" style="${style}" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="${w}" height="${h}" as="geometry"/></mxCell>\n`;
}

function edge(id, source, target, value = '') {
  const style = 'edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#222222;fontFamily=Comic Sans MS;fontSize=10;sketch=1;endArrow=block;endFill=1;labelBackgroundColor=#ffffff;';
  return `        <mxCell id="${id}" value="${esc(value)}" style="${style}" edge="1" parent="1" source="${source}" target="${target}"><mxGeometry relative="1" as="geometry"/></mxCell>\n`;
}

function browser(id, title, x, y, w = 360, h = 255) {
  let xml = '';
  xml += cell(`${id}_outer`, '', x, y, w, h, `${base}rounded=0;fillColor=#ffffff;`);
  xml += cell(`${id}_bar`, title, x, y, w, 30, `${dark}rounded=0;fontStyle=1;align=left;spacingLeft=16;`);
  xml += cell(`${id}_dot1`, '', x + w - 74, y + 10, 10, 10, `${base}ellipse;fillColor=#ffffff;`);
  xml += cell(`${id}_dot2`, '', x + w - 52, y + 10, 10, 10, `${base}ellipse;fillColor=#ffffff;`);
  xml += cell(`${id}_dot3`, '', x + w - 30, y + 10, 10, 10, `${base}ellipse;fillColor=#ffffff;`);
  return xml;
}

function appShell(id, title, x, y, w = 360, h = 255, role = 'Menu') {
  let xml = browser(id, title, x, y, w, h);
  xml += cell(`${id}_side`, `${role}\n\nDashboard\nPengajuan\nLPJ\nMaster/Approval\nProfil`, x + 10, y + 42, 82, h - 52, `${dark}rounded=0;align=left;verticalAlign=top;spacing=8;fontSize=10;`);
  xml += cell(`${id}_top`, title, x + 102, y + 42, w - 112, 32, `${base}rounded=0;fontStyle=1;align=left;spacingLeft=10;fillColor=#f8f8f8;`);
  return xml;
}

function button(id, label, x, y, w = 76, h = 24) {
  return cell(id, label, x, y, w, h, `${dark}rounded=1;arcSize=12;fontStyle=1;`);
}

function input(id, label, x, y, w = 160, h = 24) {
  return cell(id, label, x, y, w, h, `${base}rounded=0;align=left;spacingLeft=8;fontColor=#666666;`);
}

function table(id, headers, rows, x, y, w, h) {
  const rowText = `${headers.join(' | ')}\n${rows.map((r) => r.join(' | ')).join('\n')}`;
  return cell(id, rowText, x, y, w, h, `${base}rounded=0;align=left;verticalAlign=top;spacing=8;fontSize=10;`);
}

function stats(id, labels, x, y, w) {
  const gap = 10;
  const boxW = (w - gap * (labels.length - 1)) / labels.length;
  return labels.map((label, i) => cell(`${id}_${i}`, label, x + i * (boxW + gap), y, boxW, 54, `${dark}rounded=0;fontStyle=1;`)).join('');
}

function formFields(id, labels, x, y, w, columns = 1) {
  const colW = columns === 1 ? w : (w - 12) / 2;
  let xml = '';
  labels.forEach((label, i) => {
    const col = i % columns;
    const row = Math.floor(i / columns);
    xml += input(`${id}_${i}`, label, x + col * (colW + 12), y + row * 34, colW, 24);
  });
  return xml;
}

function note(id, text, x, y, w = 240, h = 70) {
  return cell(id, text, x, y, w, h, `${base}shape=note;whiteSpace=wrap;html=1;backgroundOutline=1;darkOpacity=0.05;align=left;verticalAlign=top;spacing=8;`);
}

function page(name, cells, width = 1600, height = 1050) {
  return `  <diagram id="${esc(name.toLowerCase().replace(/[^a-z0-9]+/g, '-'))}" name="${esc(name)}">\n    <mxGraphModel dx="1600" dy="1050" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="${width}" pageHeight="${height}" math="0" shadow="0">\n      <root>\n        <mxCell id="0"/>\n        <mxCell id="1" parent="0"/>\n${cells}      </root>\n    </mxGraphModel>\n  </diagram>\n`;
}

let pages = [];

{
  let xml = cell('title', 'Prototype Wireframe Low-Fidelity\nSistem Pengelolaan Kegiatan Ormawa UNUJA', 40, 30, 760, 70, titleStyle);
  xml += note('scope', 'Gaya: sketsa/Balsamiq-like. Tujuan: rancangan layar, bukan desain final.\nKomponen diambil dari route, Blade view, DFD, ERD, dan workflow pengajuan.', 920, 35, 420, 90);
  xml += browser('welcome', 'Welcome / Landing', 40, 140);
  xml += cell('welcome_h1', 'Sistem Ormawa UNUJA', 80, 205, 280, 34, `${textStyle}fontStyle=1;fontSize=20;align=center;`);
  xml += cell('welcome_copy', 'Ajukan kegiatan, pantau persetujuan, unggah LPJ', 95, 245, 250, 42, textStyle);
  xml += button('welcome_login', 'Masuk', 125, 315);
  xml += button('welcome_register', 'Daftar', 210, 315);
  xml += browser('login', 'Login', 450, 140);
  xml += cell('login_logo', 'LOGO', 585, 192, 90, 38, dark);
  xml += formFields('login_form', ['NIM / Username / Email', 'Password'], 535, 250, 190);
  xml += cell('login_remember', '[ ] Ingat saya', 535, 322, 130, 22, textStyle);
  xml += button('login_btn', 'Masuk', 650, 320);
  xml += browser('register', 'Registrasi', 860, 140);
  xml += formFields('register_form', ['Nama', 'Email', 'NIM', 'Password', 'Konfirmasi Password'], 930, 198, 210);
  xml += button('register_btn', 'Daftar', 1060, 382);
  xml += browser('forgot', 'Lupa / Reset Password', 40, 470);
  xml += formFields('forgot_form', ['Email', 'Password Baru', 'Konfirmasi Password'], 110, 535, 210);
  xml += button('forgot_btn', 'Kirim / Reset', 220, 662, 100);
  xml += browser('verify', 'Verifikasi Email', 450, 470);
  xml += cell('verify_copy', 'Pesan verifikasi akun\n\n[ Kirim ulang email ]', 530, 548, 210, 100, `${base}rounded=0;`);
  xml += browser('initial', 'Ganti Password Awal', 860, 470);
  xml += formFields('initial_form', ['Password saat ini', 'Password baru', 'Konfirmasi password'], 930, 535, 210);
  xml += button('initial_btn', 'Simpan', 1068, 662);
  pages.push(page('00 Auth dan Halaman Awal', xml));
}

{
  let xml = cell('title', 'Dashboard Per Role', 40, 30, 500, 45, titleStyle);
  const cards = [
    ['admin', 'Dashboard Admin', 'Admin', ['Total Ormawa', 'Pengajuan Aktif', 'User', 'LPJ'], ['Grafik status', 'Aktivitas terbaru']],
    ['ormawa', 'Dashboard Ormawa', 'Ormawa', ['Draft', 'Diajukan', 'Revisi', 'Disetujui'], ['CTA Buat Pengajuan', 'Timeline pengajuan']],
    ['mhs', 'Dashboard Mahasiswa', 'Mahasiswa', ['Ormawa Aktif', 'Kegiatan', 'LPJ', 'Notifikasi'], ['Pilih Ormawa aktif', 'Tugas organisasi']],
    ['bauak', 'Dashboard BAUAK', 'BAUAK', ['Menunggu Verifikasi', 'Terverifikasi', 'LPJ Masuk', 'Revisi'], ['Antrian verifikasi', 'Laporan ringkas']],
    ['approver', 'Dashboard Approver', 'Kaprodi/Dekan/Warek/Rektor/PP', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai'], ['Daftar persetujuan', 'Dokumen untuk dicek']],
  ];
  cards.forEach(([id, title, role, labels, extras], idx) => {
    const x = 40 + (idx % 2) * 760;
    const y = 110 + Math.floor(idx / 2) * 310;
    xml += appShell(id, title, x, y, 700, 260, role);
    xml += stats(`${id}_stats`, labels, x + 112, y + 90, 560);
    xml += cell(`${id}_main`, extras.join('\n\n'), x + 112, y + 160, 360, 78, `${base}rounded=0;align=left;spacing=8;`);
    xml += table(`${id}_table`, ['Judul', 'Status', 'Aksi'], [['Kegiatan A', 'Menunggu', 'Detail'], ['Kegiatan B', 'Revisi', 'Buka']], x + 486, y + 160, 186, 78);
  });
  pages.push(page('01 Dashboard Role', xml));
}

{
  let xml = cell('title', 'Modul Pengajuan Kegiatan', 40, 30, 520, 45, titleStyle);
  xml += appShell('idx', 'Daftar Pengajuan', 40, 110, 700, 280, 'Ormawa/BAUAK/Admin');
  xml += input('idx_search', 'Cari judul / ormawa / status', 160, 185, 260);
  xml += button('idx_new', '+ Baru', 600, 185, 70);
  xml += table('idx_table', ['Judul', 'Ormawa', 'Tanggal', 'Status', 'Aksi'], [['Seminar', 'BEM', '10 Jul', 'BAUAK', 'Detail'], ['Pelatihan', 'HIMA', '12 Jul', 'Revisi', 'Edit']], 160, 225, 510, 120);
  xml += appShell('form', 'Form Pengajuan Baru/Edit', 800, 110, 700, 280, 'Ormawa/Mahasiswa');
  xml += formFields('pengajuan_form', ['Judul kegiatan', 'Tujuan kegiatan', 'Lokasi', 'Tanggal mulai', 'Tanggal selesai', 'Ketua pelaksana', 'Nama pemohon'], 920, 185, 510, 2);
  xml += cell('uploads', 'Upload proposal\n[ pilih file ]\n\nUpload RAB\n[ pilih file ]', 920, 315, 245, 58, `${base}rounded=0;align=left;spacing=8;`);
  xml += button('save_draft', 'Simpan Draft', 1260, 330, 100);
  xml += button('submit', 'Ajukan', 1370, 330, 70);
  xml += appShell('show', 'Detail Pengajuan', 40, 460, 700, 330, 'Semua role');
  xml += cell('show_summary', 'Judul: Seminar Nasional\nOrmawa: BEM\nStatus: Menunggu Verifikasi BAUAK\nCatatan terakhir: -', 160, 540, 270, 92, `${base}rounded=0;align=left;spacing=8;`);
  xml += cell('timeline', 'Draft > BAUAK > Kaprodi > Dekan > Warek III > Rektor > PP > Selesai', 450, 540, 220, 92, `${base}rounded=0;`);
  xml += table('docs', ['Dokumen', 'Versi', 'Aksi'], [['Proposal.pdf', 'v2', 'Unduh'], ['RAB.xlsx', 'v1', 'Unduh']], 160, 650, 510, 70);
  xml += appShell('print', 'Cetak / Export Pengajuan', 800, 460, 700, 330, 'Semua role');
  xml += cell('print_page', 'SURAT / RINGKASAN PENGAJUAN\n\nData kegiatan\nData ormawa\nRAB ringkas\nStatus persetujuan', 950, 540, 330, 190, `${base}rounded=0;align=center;verticalAlign=top;spacing=12;`);
  xml += button('print_btn', 'Print', 1310, 550);
  xml += button('csv_btn', 'Export CSV', 1310, 585, 90);
  pages.push(page('02 Pengajuan Kegiatan', xml));
}

{
  let xml = cell('title', 'Proposal, RAB, dan Dokumen', 40, 30, 520, 45, titleStyle);
  xml += appShell('proposal_index', 'Daftar Proposal Kegiatan', 40, 110, 700, 280, 'Ormawa');
  xml += input('proposal_search', 'Cari proposal', 160, 185, 240);
  xml += button('proposal_new', '+ Upload', 590, 185, 80);
  xml += table('proposal_table', ['Judul', 'Versi', 'Status', 'Aksi'], [['Proposal Seminar', 'v2', 'Aktif', 'Detail'], ['Proposal Bakti Sosial', 'v1', 'Draft', 'Edit']], 160, 225, 510, 120);
  xml += appShell('proposal_create', 'Upload Proposal / RAB', 800, 110, 700, 280, 'Ormawa');
  xml += formFields('proposal_fields', ['Pilih pengajuan', 'File proposal', 'File RAB', 'Total anggaran'], 920, 185, 510, 2);
  xml += cell('version_note', 'Versi dokumen otomatis bertambah.\nRiwayat versi tetap disimpan.', 920, 282, 260, 70, `${base}shape=note;align=left;verticalAlign=top;spacing=8;`);
  xml += button('proposal_save', 'Simpan', 1330, 325);
  xml += appShell('proposal_show', 'Detail Proposal', 40, 460, 700, 280, 'Ormawa');
  xml += table('version_table', ['Dokumen', 'Versi', 'Status', 'Tanggal'], [['Proposal.pdf', 'v1', 'Lama', '01 Jul'], ['Proposal-revisi.pdf', 'v2', 'Aktif', '05 Jul']], 160, 540, 510, 120);
  pages.push(page('03 Proposal dan RAB', xml));
}

{
  let xml = cell('title', 'Verifikasi dan Persetujuan Berjenjang', 40, 30, 660, 45, titleStyle);
  xml += appShell('bauak_idx', 'BAUAK - Antrian Verifikasi', 40, 110, 700, 280, 'BAUAK');
  xml += table('bauak_table', ['Pilih', 'Judul', 'Kelengkapan', 'Status', 'Aksi'], [['[ ]', 'Seminar', 'Lengkap', 'Menunggu', 'Cek'], ['[ ]', 'Pelatihan', 'Kurang RAB', 'Revisi', 'Cek']], 160, 190, 510, 120);
  xml += button('bulk', 'Bulk Verify', 560, 325, 110);
  xml += appShell('bauak_show', 'BAUAK - Detail Verifikasi', 800, 110, 700, 280, 'BAUAK');
  xml += cell('checklist', '[x] Proposal\n[x] RAB\n[ ] Tanggal valid\n[x] Data ormawa', 920, 190, 220, 110, `${base}rounded=0;align=left;spacing=8;`);
  xml += input('bauak_note', 'Catatan verifikasi', 1160, 190, 260, 76);
  xml += button('verify_ok', 'Verifikasi', 1160, 300, 90);
  xml += button('verify_reject', 'Minta Revisi', 1260, 300, 110);
  xml += appShell('approval_idx', 'Approver - Daftar Persetujuan', 40, 460, 700, 280, 'Kaprodi/Dekan/Warek/Rektor/PP');
  xml += table('approval_table', ['Judul', 'Ormawa', 'Tahap', 'Aksi'], [['Seminar', 'BEM', 'Kaprodi', 'Review'], ['Bakti Sosial', 'UKM', 'Dekan', 'Review']], 160, 540, 510, 120);
  xml += appShell('approval_show', 'Approver - Review Dokumen', 800, 460, 700, 280, 'Kaprodi/Dekan/Warek/Rektor/PP');
  xml += cell('approval_doc', 'Preview dokumen\nProposal + RAB', 920, 540, 220, 110, `${base}rounded=0;`);
  xml += input('approval_note', 'Catatan keputusan', 1160, 540, 260, 76);
  xml += button('approve', 'Setujui', 1160, 650);
  xml += button('reject', 'Tolak/Revisi', 1250, 650, 100);
  pages.push(page('04 Verifikasi Persetujuan', xml));
}

{
  let xml = cell('title', 'LPJ Kegiatan', 40, 30, 420, 45, titleStyle);
  xml += appShell('lpj_idx', 'Daftar LPJ', 40, 110, 700, 280, 'Semua role');
  xml += input('lpj_filter', 'Filter status / kegiatan', 160, 185, 230);
  xml += table('lpj_table', ['Kegiatan', 'Pelaksanaan', 'Status', 'Aksi'], [['Seminar', '10-11 Jul', 'Draft', 'Lengkapi'], ['Pelatihan', '15 Jul', 'Menunggu BAUAK', 'Detail']], 160, 225, 510, 120);
  xml += appShell('lpj_form', 'Form LPJ', 800, 110, 700, 330, 'Ormawa/Mahasiswa');
  xml += formFields('lpj_fields', ['Ringkasan pelaksanaan', 'Hasil kegiatan', 'Kendala', 'Tanggal mulai', 'Tanggal selesai', 'Jumlah peserta', 'Realisasi anggaran', 'Sisa anggaran'], 920, 185, 510, 2);
  xml += cell('lpj_upload', 'Lampiran / file laporan\n[ tambah lampiran ]', 920, 335, 245, 60, `${base}rounded=0;`);
  xml += button('lpj_save', 'Simpan LPJ', 1320, 365, 100);
  xml += appShell('lpj_show', 'Detail LPJ', 40, 510, 700, 300, 'Semua role');
  xml += table('lpj_budget', ['Uraian', 'Rencana', 'Realisasi', 'Ket.'], [['Konsumsi', '1.000.000', '950.000', 'OK'], ['Transport', '500.000', '520.000', 'Selisih']], 160, 590, 510, 90);
  xml += cell('lpj_files', 'Lampiran foto\nDokumen LPJ v2\nRiwayat verifikasi', 160, 695, 510, 55, `${base}rounded=0;`);
  xml += appShell('lpj_verify', 'BAUAK - Verifikasi LPJ', 800, 510, 700, 300, 'BAUAK');
  xml += cell('lpj_check', '[x] Laporan lengkap\n[x] Realisasi anggaran\n[ ] Lampiran kegiatan', 920, 590, 230, 90, `${base}rounded=0;align=left;spacing=8;`);
  xml += input('lpj_note', 'Catatan verifikator', 1170, 590, 260, 70);
  xml += button('lpj_ok', 'Terima', 1170, 690);
  xml += button('lpj_revision', 'Revisi', 1260, 690);
  pages.push(page('05 LPJ', xml));
}

{
  let xml = cell('title', 'Ormawa, Keanggotaan, dan Permintaan Bergabung', 40, 30, 760, 45, titleStyle);
  xml += appShell('ormawa_idx', 'Kelola Ormawa', 40, 110, 700, 280, 'Admin/BAUAK');
  xml += input('ormawa_search', 'Cari ormawa / ketua', 160, 185, 240);
  xml += button('ormawa_new', '+ Ormawa', 590, 185, 80);
  xml += table('ormawa_table', ['Nama', 'Ketua', 'Tingkat', 'Periode', 'Aksi'], [['BEM Fakultas', 'Ahmad', 'Fakultas', '2026', 'Detail'], ['HIMA TI', 'Siti', 'Prodi', '2026', 'Edit']], 160, 225, 510, 120);
  xml += appShell('ormawa_form', 'Form Ormawa', 800, 110, 700, 280, 'Admin/BAUAK');
  xml += formFields('ormawa_fields', ['Nama Ormawa', 'Ketua', 'Fakultas', 'Program Studi', 'Kategori', 'Tingkat', 'Periode', 'Kontak'], 920, 185, 510, 2);
  xml += button('ormawa_save', 'Simpan', 1340, 330);
  xml += appShell('anggota_idx', 'Kelola Anggota', 40, 460, 700, 300, 'Ketua/Wakil/Admin');
  xml += input('anggota_search', 'Cari mahasiswa', 160, 535, 230);
  xml += table('anggota_table', ['Nama', 'NIM', 'Jabatan', 'Status', 'Aksi'], [['Ali', '123', 'Ketua', 'Aktif', 'Edit'], ['Bela', '124', 'Bendahara', 'Aktif', 'Edit']], 160, 575, 510, 110);
  xml += button('anggota_add', '+ Anggota', 570, 700, 100);
  xml += appShell('requests', 'Permintaan Bergabung / Pilih Ormawa Aktif', 800, 460, 700, 300, 'Mahasiswa');
  xml += cell('active_org', 'Ormawa aktif saat ini\n[ HIMA TI v ]', 920, 535, 240, 70, `${base}rounded=0;`);
  xml += table('request_table', ['Ormawa', 'Jabatan diminta', 'Status', 'Aksi'], [['BEM', 'Anggota', 'Menunggu', '-'], ['UKM Seni', 'Anggota', 'Diterima', 'Aktifkan']], 920, 625, 510, 90);
  pages.push(page('06 Ormawa dan Anggota', xml));
}

{
  let xml = cell('title', 'Admin Master Data dan Laporan', 40, 30, 620, 45, titleStyle);
  xml += appShell('akademik', 'Kelola Akademik', 40, 110, 700, 300, 'Admin');
  xml += stats('akademik_stats', ['Fakultas', 'Program Studi', 'Dekan', 'Kaprodi'], 160, 185, 510);
  xml += table('akademik_tables', ['Modul', 'Data', 'Aksi'], [['Fakultas', '8 data', 'Kelola'], ['Prodi', '32 data', 'Kelola'], ['Dekan', '8 akun', 'Kelola'], ['Kaprodi', '32 akun', 'Kelola']], 160, 260, 510, 105);
  xml += appShell('master_form', 'Form Fakultas / Prodi / Dekan / Kaprodi', 800, 110, 700, 300, 'Admin');
  xml += formFields('master_fields', ['Nama', 'Kode', 'Fakultas', 'User pejabat', 'Profile URL', 'Status lainnya'], 920, 185, 510, 2);
  xml += button('master_save', 'Simpan', 1340, 320);
  xml += appShell('mahasiswa', 'Mahasiswa Tersinkron', 40, 480, 700, 280, 'Admin');
  xml += input('mhs_search', 'Cari NIM / nama / prodi', 160, 555, 240);
  xml += table('mhs_table', ['NIM', 'Nama', 'Prodi', 'Status', 'Aksi'], [['123', 'Ali', 'TI', 'Aktif', 'Reset PW'], ['124', 'Bela', 'SI', 'Aktif', 'Detail']], 160, 595, 510, 100);
  xml += appShell('laporan', 'Laporan Admin/BAUAK', 800, 480, 700, 280, 'Admin/BAUAK');
  xml += formFields('laporan_filter', ['Periode', 'Fakultas', 'Status', 'Jenis laporan'], 920, 555, 510, 2);
  xml += cell('laporan_chart', 'Grafik / rekap tabel laporan', 920, 635, 320, 80, `${base}rounded=0;`);
  xml += button('laporan_export', 'Export', 1320, 665);
  pages.push(page('07 Admin Master Data', xml));
}

{
  let xml = cell('title', 'Profil, Notifikasi, Telegram, dan Email', 40, 30, 660, 45, titleStyle);
  xml += appShell('profile', 'Pengaturan Profil', 40, 110, 700, 320, 'Semua role');
  xml += formFields('profile_fields', ['Nama', 'Email', 'No HP', 'Username/NIM', 'Password lama', 'Password baru'], 160, 185, 510, 2);
  xml += cell('telegram_box', 'Koneksi Telegram\nStatus: belum terhubung\n[ Buat kode OTP ] [ Putuskan ]', 160, 300, 260, 80, `${base}rounded=0;align=left;spacing=8;`);
  xml += button('profile_save', 'Simpan', 590, 350);
  xml += appShell('notif_index', 'Daftar Notifikasi', 800, 110, 700, 320, 'Semua role');
  xml += table('notif_table', ['Dibaca', 'Judul', 'Tipe', 'Waktu', 'Aksi'], [['Belum', 'Pengajuan direvisi', 'Pengajuan', 'Hari ini', 'Buka'], ['Ya', 'LPJ diterima', 'LPJ', 'Kemarin', 'Hapus']], 920, 185, 510, 120);
  xml += button('read_all', 'Tandai Semua', 1230, 325, 120);
  xml += cell('dropdown', 'Dropdown notifikasi di topbar\n\n- 5 terbaru\n- badge jumlah belum dibaca\n- klik menuju detail', 920, 325, 260, 80, `${base}shape=note;align=left;verticalAlign=top;spacing=8;`);
  xml += appShell('email', 'Template Email Notifikasi', 40, 500, 700, 260, 'Sistem');
  xml += cell('email_body', 'Subjek: update pengajuan\n\nHalo, ada perubahan status.\n\n[ Lihat detail ]', 210, 580, 360, 110, `${base}rounded=0;align=left;spacing=12;`);
  pages.push(page('08 Profil Notifikasi', xml));
}

const xml = `<mxfile host="app.diagrams.net" modified="2026-07-09T00:00:00.000Z" agent="Codex" version="24.7.17">\n${pages.join('')}</mxfile>\n`;

fs.writeFileSync(output, xml);
console.log(`Generated ${output}`);
