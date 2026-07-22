import fs from 'node:fs';

const output = new URL('../docs/flowchart-pengajuan-kegiatan.drawio', import.meta.url);
const now = new Date().toISOString();

function esc(value = '') {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');
}

function cell(id, value, style, x, y, width, height) {
  return `<mxCell id="${id}" value="${esc(value)}" style="${style}" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="${width}" height="${height}" as="geometry"/></mxCell>`;
}

function edge(id, source, target, label = '') {
  const style = 'edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;endArrow=block;endFill=1;strokeWidth=1.2;';
  return `<mxCell id="${id}" value="${esc(label)}" style="${style}" edge="1" parent="1" source="${source}" target="${target}"><mxGeometry relative="1" as="geometry"/></mxCell>`;
}

const styles = {
  title: 'text;html=1;align=left;verticalAlign=middle;fontSize=16;fontStyle=1;whiteSpace=wrap;strokeColor=#000000;fillColor=#ffffff;spacingLeft=12;',
  lane: 'swimlane;html=1;horizontal=1;startSize=38;fillColor=#ffffff;swimlaneFillColor=#ffffff;strokeColor=#000000;fontStyle=1;fontSize=14;',
  start: 'ellipse;whiteSpace=wrap;html=1;aspect=fixed;strokeColor=#000000;fillColor=#ffffff;',
  input: 'shape=parallelogram;perimeter=parallelogramPerimeter;whiteSpace=wrap;html=1;strokeColor=#000000;fillColor=#ffffff;',
  process: 'rounded=0;whiteSpace=wrap;html=1;strokeColor=#000000;fillColor=#ffffff;',
  dashboard: 'rounded=1;arcSize=35;whiteSpace=wrap;html=1;strokeColor=#000000;fillColor=#ffffff;fontStyle=1;',
  decision: 'rhombus;whiteSpace=wrap;html=1;strokeColor=#000000;fillColor=#ffffff;',
  database: 'shape=cylinder3;whiteSpace=wrap;html=1;boundedLbl=1;backgroundOutline=1;size=15;strokeColor=#000000;fillColor=#ffffff;fontSize=11;',
};

function diagram(name, id, height, cells) {
  return `<diagram id="${id}" name="${esc(name)}"><mxGraphModel dx="1400" dy="900" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1169" pageHeight="${height}" math="0" shadow="0"><root><mxCell id="0"/><mxCell id="1" parent="0"/>${cells.join('')}</root></mxGraphModel></diagram>`;
}

function buildRolePage({ id, name, actor, menus }) {
  const maxTables = Math.max(...menus.map((menu) => menu.tables.length), 1);
  const gap = Math.max(190, 105 + maxTables * 48);
  const firstMenuY = 410;
  const logoutY = firstMenuY + menus.length * gap;
  const height = logoutY + 300;
  const cells = [];
  let seq = 0;
  const next = (prefix) => `${id}-${prefix}-${++seq}`;

  cells.push(cell(next('title'), `Flowchart ${name}`, styles.title, 20, 20, 1120, 42));
  cells.push(cell(next('lane'), actor, styles.lane, 20, 72, 560, height - 110));
  cells.push(cell(next('lane'), 'Sistem dan Tabel Penyimpanan', styles.lane, 580, 72, 560, height - 110));

  const start = next('start');
  const login = next('login');
  const validate = next('validate');
  const users = next('users');
  const valid = next('valid');
  const failed = next('failed');
  const dashboard = next('dashboard');

  cells.push(cell(start, 'Mulai', styles.start, 260, 135, 80, 80));
  cells.push(cell(login, 'Email dan password', styles.input, 205, 240, 190, 58));
  cells.push(cell(validate, 'Validasi akun dan role', styles.process, 655, 220, 220, 58));
  cells.push(cell(users, 'Tabel users', styles.database, 945, 210, 155, 70));
  cells.push(cell(valid, 'Login valid?', styles.decision, 715, 305, 105, 85));
  cells.push(cell(failed, 'Tampilkan pesan login gagal', styles.process, 900, 320, 190, 55));
  cells.push(cell(dashboard, `Dashboard ${actor}`, styles.dashboard, 220, 320, 160, 62));

  cells.push(edge(next('edge'), start, login));
  cells.push(edge(next('edge'), login, validate));
  cells.push(edge(next('edge'), validate, users));
  cells.push(edge(next('edge'), users, valid));
  cells.push(edge(next('edge'), valid, dashboard, 'Ya'));
  cells.push(edge(next('edge'), valid, failed, 'Tidak'));
  cells.push(edge(next('edge'), failed, login));

  let previousDecision = null;
  menus.forEach((menu, index) => {
    const y = firstMenuY + index * gap;
    const decision = next('menu');
    const action = next('action');
    const process = next('process');

    cells.push(cell(decision, menu.decision, styles.decision, 245, y, 115, 90));
    cells.push(cell(action, menu.action, menu.input === false ? styles.process : styles.input, 190, y + 110, 225, 62));
    cells.push(cell(process, menu.process, styles.process, 630, y + 105, 255, 70));

    if (previousDecision) {
      cells.push(edge(next('edge'), previousDecision, decision, 'Tidak'));
    } else {
      cells.push(edge(next('edge'), dashboard, decision));
    }
    cells.push(edge(next('edge'), decision, action, 'Ya'));
    cells.push(edge(next('edge'), action, process));

    let last = process;
    menu.tables.forEach((table, tableIndex) => {
      const database = next('db');
      cells.push(cell(database, `Tabel ${table}`, styles.database, 945, y + 92 + tableIndex * 48, 155, 62));
      cells.push(edge(next('edge'), last, database));
      last = database;
    });
    cells.push(edge(next('edge'), last, dashboard));
    previousDecision = decision;
  });

  const logout = next('logout');
  const clearSession = next('clear');
  const sessions = next('sessions');
  const log = next('log');
  const logTable = next('logtable');
  const finish = next('finish');

  cells.push(cell(logout, 'Keluar?', styles.decision, 245, logoutY, 115, 90));
  cells.push(cell(clearSession, 'Hapus sesi pengguna', styles.process, 630, logoutY + 5, 220, 58));
  cells.push(cell(sessions, 'Tabel sessions', styles.database, 945, logoutY - 10, 155, 62));
  cells.push(cell(log, 'Catat aktivitas keluar', styles.process, 630, logoutY + 90, 220, 58));
  cells.push(cell(logTable, 'Tabel log_aktivitas', styles.database, 945, logoutY + 82, 155, 70));
  cells.push(cell(finish, 'Selesai', styles.start, 260, logoutY + 165, 80, 80));
  cells.push(edge(next('edge'), previousDecision ?? dashboard, logout, menus.length ? 'Tidak' : ''));
  cells.push(edge(next('edge'), logout, dashboard, 'Tidak'));
  cells.push(edge(next('edge'), logout, clearSession, 'Ya'));
  cells.push(edge(next('edge'), clearSession, sessions));
  cells.push(edge(next('edge'), sessions, log));
  cells.push(edge(next('edge'), log, logTable));
  cells.push(edge(next('edge'), logTable, finish));

  return diagram(name, id, height, cells);
}

function buildMainPage() {
  const id = 'main';
  const height = 4020;
  const cells = [];
  let seq = 0;
  const next = (prefix) => `${id}-${prefix}-${++seq}`;
  const nodes = {};
  const add = (key, value, style, x, y, width, nodeHeight) => {
    nodes[key] = next(key);
    cells.push(cell(nodes[key], value, style, x, y, width, nodeHeight));
  };
  const link = (from, to, label = '') => cells.push(edge(next('edge'), nodes[from], nodes[to], label));

  cells.push(cell(next('title'), 'Flowchart Pengajuan Kegiatan Sesuai Implementasi Sistem', styles.title, 20, 20, 1120, 42));
  cells.push(cell(next('lane'), 'Pemohon', styles.lane, 20, 72, 510, 3860));
  cells.push(cell(next('lane'), 'Sistem dan Pemeriksa', styles.lane, 530, 72, 610, 3860));

  add('start', 'Dashboard', styles.dashboard, 190, 120, 150, 62);
  add('menu', 'Pilih menu Pengajuan', styles.input, 160, 220, 210, 58);
  add('chooseOrmawa', 'Pilih Ormawa aktif', styles.input, 160, 320, 210, 58);
  add('access', 'Cek role, keanggotaan, dan hak akses Ormawa', styles.process, 570, 310, 280, 70);
  add('ormawa', 'Tabel ormawa', styles.database, 920, 275, 160, 62);
  add('membership', 'Tabel anggota_ormawa', styles.database, 920, 350, 160, 62);
  add('allowed', 'Hak akses valid?', styles.decision, 650, 420, 120, 95);
  add('accessDenied', 'Pilih Ormawa lain atau lengkapi keanggotaan', styles.input, 135, 430, 260, 70);
  add('activity', 'Isi data kegiatan', styles.input, 165, 560, 200, 58);
  add('uploadProposal', 'Upload proposal PDF', styles.input, 165, 650, 200, 58);
  add('uploadRab', 'Upload RAB PDF', styles.input, 165, 740, 200, 58);
  add('validateForm', 'Validasi data dan berkas', styles.process, 590, 830, 240, 62);
  add('complete', 'Data lengkap dan valid?', styles.decision, 650, 920, 120, 95);
  add('submit', 'Submit pengajuan', styles.input, 165, 930, 200, 58);
  add('save', 'Simpan status menunggu_dosen', styles.process, 580, 1050, 260, 65);
  add('pengajuan', 'Tabel pengajuan_kegiatan', styles.database, 920, 1010, 160, 62);
  add('proposal', 'Tabel proposal', styles.database, 920, 1080, 160, 62);
  add('rab', 'Tabel rab', styles.database, 920, 1150, 160, 62);
  add('notifyDosen', 'Kirim notifikasi kepada Dosen Pembina', styles.process, 570, 1170, 280, 65);
  add('dosen', 'Dosen Pembina memverifikasi', styles.process, 580, 1280, 260, 65);
  add('vdosen', 'Tabel verifikasi_dosen', styles.database, 920, 1280, 160, 62);
  add('dosenDecision', 'Keputusan Dosen?', styles.decision, 650, 1380, 120, 95);
  add('reviseDosen', 'Mahasiswa edit dan submit ulang', styles.input, 145, 1390, 240, 65);
  add('level', 'Ormawa tingkat fakultas?', styles.decision, 650, 1510, 130, 100);
  add('waitDekan', 'Status menunggu_dekan', styles.process, 580, 1640, 260, 60);
  add('dekan', 'Dekan memeriksa pengajuan', styles.process, 580, 1730, 260, 65);
  add('pdekan', 'Tabel persetujuan_dekan', styles.database, 920, 1730, 160, 62);
  add('dekanDecision', 'Disetujui Dekan?', styles.decision, 650, 1830, 120, 95);
  add('waitBauak', 'Status menunggu_bauak', styles.process, 580, 1960, 260, 60);
  add('bauak', 'BAUAK memverifikasi administrasi', styles.process, 570, 2050, 280, 65);
  add('vbauak', 'Tabel verifikasi_bauak', styles.database, 920, 2050, 160, 62);
  add('bauakDecision', 'Keputusan BAUAK?', styles.decision, 650, 2150, 120, 95);
  add('reviseBauak', 'Mahasiswa edit dan submit ulang', styles.input, 145, 2160, 240, 65);
  add('waitWarek', 'Status menunggu_warek3', styles.process, 580, 2280, 260, 60);
  add('warek', 'Warek III memeriksa pengajuan', styles.process, 580, 2370, 260, 65);
  add('pwarek', 'Tabel persetujuan_warek3', styles.database, 920, 2370, 160, 62);
  add('warekDecision', 'Disetujui Warek III?', styles.decision, 650, 2470, 125, 100);
  add('waitRektor', 'Status menunggu_rektor', styles.process, 580, 2600, 260, 60);
  add('rektor', 'Rektor melakukan persetujuan akhir', styles.process, 570, 2690, 280, 65);
  add('prektor', 'Tabel persetujuan_rektor', styles.database, 920, 2690, 160, 62);
  add('rektorDecision', 'Disetujui Rektor?', styles.decision, 650, 2790, 125, 100);
  add('waitPp', 'Status menunggu_pp', styles.process, 580, 2920, 260, 60);
  add('pp', 'Kepala/Wakil PP melakukan persetujuan akhir', styles.process, 560, 3010, 300, 70);
  add('ppTable', 'Tabel persetujuan_pp', styles.database, 920, 3010, 160, 62);
  add('ppDecision', 'Disetujui Kepala/Wakil PP?', styles.decision, 645, 3110, 135, 105);
  add('approved', 'Status disetujui', styles.process, 590, 3240, 240, 60);
  add('rejected', 'Simpan status penolakan sesuai tahap dan catatan', styles.process, 860, 3225, 240, 80);
  add('notification', 'Kirim notifikasi hasil kepada Pemohon', styles.process, 570, 3360, 280, 65);
  add('notifTable', 'Tabel notifikasi', styles.database, 920, 3360, 160, 62);
  add('result', 'Lihat status dan catatan pengajuan', styles.input, 145, 3480, 240, 65);
  add('finish', 'Selesai', styles.start, 225, 3600, 80, 80);

  link('start', 'menu'); link('menu', 'chooseOrmawa'); link('chooseOrmawa', 'access');
  link('access', 'ormawa'); link('access', 'membership'); link('ormawa', 'allowed'); link('membership', 'allowed');
  link('allowed', 'accessDenied', 'Tidak'); link('accessDenied', 'chooseOrmawa'); link('allowed', 'activity', 'Ya');
  link('activity', 'uploadProposal'); link('uploadProposal', 'uploadRab'); link('uploadRab', 'validateForm');
  link('validateForm', 'complete'); link('complete', 'activity', 'Tidak'); link('complete', 'submit', 'Ya');
  link('submit', 'save'); link('save', 'pengajuan'); link('save', 'proposal'); link('save', 'rab'); link('save', 'notifyDosen');
  link('notifyDosen', 'dosen'); link('dosen', 'vdosen'); link('vdosen', 'dosenDecision');
  link('dosenDecision', 'reviseDosen', 'Revisi'); link('reviseDosen', 'activity');
  link('dosenDecision', 'rejected', 'Tolak: ditolak_dosen'); link('dosenDecision', 'level', 'Setujui');
  link('level', 'waitDekan', 'Ya'); link('level', 'waitBauak', 'Tidak'); link('waitDekan', 'dekan');
  link('dekan', 'pdekan'); link('pdekan', 'dekanDecision'); link('dekanDecision', 'rejected', 'Tolak: ditolak_dekan'); link('dekanDecision', 'waitBauak', 'Setujui');
  link('waitBauak', 'bauak'); link('bauak', 'vbauak'); link('vbauak', 'bauakDecision');
  link('bauakDecision', 'reviseBauak', 'Revisi'); link('reviseBauak', 'activity'); link('bauakDecision', 'rejected', 'Tolak: ditolak_bauak'); link('bauakDecision', 'waitWarek', 'Setujui');
  link('waitWarek', 'warek'); link('warek', 'pwarek'); link('pwarek', 'warekDecision'); link('warekDecision', 'rejected', 'Tolak: ditolak_warek3'); link('warekDecision', 'waitRektor', 'Setujui');
  link('waitRektor', 'rektor'); link('rektor', 'prektor'); link('prektor', 'rektorDecision'); link('rektorDecision', 'rejected', 'Tolak: ditolak_rektor'); link('rektorDecision', 'waitPp', 'Setujui');
  link('waitPp', 'pp'); link('pp', 'ppTable'); link('ppTable', 'ppDecision'); link('ppDecision', 'rejected', 'Tolak: ditolak_pp'); link('ppDecision', 'approved', 'Setujui');
  link('approved', 'pengajuan'); link('rejected', 'pengajuan'); link('approved', 'notification'); link('rejected', 'notification');
  link('notification', 'notifTable'); link('notifTable', 'result'); link('result', 'finish');

  return diagram('01 - Alur Utama', id, height, cells);
}

const pages = [
  buildMainPage(),
  buildRolePage({
    id: 'pemohon', name: '02 - Pemohon', actor: 'Mahasiswa / Ormawa',
    menus: [
      { decision: 'Menu Pengajuan?', action: 'Isi data kegiatan dan unggah berkas', process: 'Validasi dan simpan pengajuan', tables: ['pengajuan_kegiatan', 'proposal', 'rab', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Revisi pengajuan?', action: 'Perbaiki data, proposal, dan RAB', process: 'Simpan revisi dan kembalikan ke pemeriksa', tables: ['pengajuan_kegiatan', 'proposal', 'rab', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Lihat status?', action: 'Pilih pengajuan', process: 'Muat detail dan riwayat persetujuan', tables: ['pengajuan_kegiatan', 'verifikasi_dosen', 'persetujuan_dekan', 'verifikasi_bauak', 'persetujuan_warek3', 'persetujuan_rektor'] },
      { decision: 'Menu Profil?', action: 'Ubah profil Ormawa', process: 'Validasi dan perbarui profil', tables: ['users', 'ormawa', 'log_aktivitas'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'dosen', name: '03 - Dosen Pembina', actor: 'Dosen Pembina',
    menus: [
      { decision: 'Menu Verifikasi?', action: 'Pilih pengajuan dan keputusan', process: 'Validasi kewenangan, simpan keputusan, ubah status', tables: ['pengajuan_kegiatan', 'verifikasi_dosen', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Ormawa binaan?', action: 'Pilih Ormawa binaan', process: 'Muat profil dan riwayat pengajuan', tables: ['ormawa', 'pengajuan_kegiatan', 'log_aktivitas'] },
      { decision: 'Riwayat verifikasi?', action: 'Pilih riwayat', process: 'Muat hasil verifikasi terdahulu', tables: ['verifikasi_dosen', 'pengajuan_kegiatan'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'dekan', name: '04 - Dekan', actor: 'Dekan',
    menus: [
      { decision: 'Menu Persetujuan?', action: 'Pilih pengajuan dan keputusan', process: 'Validasi fakultas, simpan keputusan, ubah status', tables: ['pengajuan_kegiatan', 'persetujuan_dekan', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Daftar Ormawa?', action: 'Pilih Ormawa fakultas', process: 'Muat profil dan riwayat pengajuan', tables: ['fakultas', 'ormawa', 'pengajuan_kegiatan'] },
      { decision: 'Riwayat persetujuan?', action: 'Pilih riwayat', process: 'Muat hasil persetujuan terdahulu', tables: ['persetujuan_dekan', 'pengajuan_kegiatan'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'bauak', name: '05 - BAUAK', actor: 'BAUAK',
    menus: [
      { decision: 'Menu Verifikasi?', action: 'Pilih pengajuan dan keputusan', process: 'Simpan verifikasi dan tetapkan berkas final', tables: ['pengajuan_kegiatan', 'proposal', 'rab', 'verifikasi_bauak', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Semua pengajuan?', action: 'Cari, filter, cetak, atau ekspor', process: 'Muat dan olah data pengajuan', tables: ['pengajuan_kegiatan', 'proposal', 'rab', 'log_aktivitas'] },
      { decision: 'Data Ormawa?', action: 'Tambah atau ubah Ormawa dan anggota', process: 'Validasi dan simpan data Ormawa', tables: ['ormawa', 'anggota_ormawa', 'users', 'log_aktivitas'] },
      { decision: 'Menu Laporan?', action: 'Pilih filter laporan', process: 'Susun statistik dan laporan pengajuan', tables: ['pengajuan_kegiatan', 'ormawa', 'verifikasi_bauak', 'log_aktivitas'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'warek', name: '06 - Wakil Rektor III', actor: 'Wakil Rektor III',
    menus: [
      { decision: 'Menu Persetujuan?', action: 'Pilih pengajuan dan keputusan', process: 'Simpan keputusan dan teruskan ke Rektor', tables: ['pengajuan_kegiatan', 'persetujuan_warek3', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Menu Monitoring?', action: 'Pilih periode atau Ormawa', process: 'Filter kegiatan yang telah disetujui', tables: ['pengajuan_kegiatan', 'ormawa', 'log_aktivitas'] },
      { decision: 'Riwayat persetujuan?', action: 'Pilih riwayat', process: 'Muat hasil persetujuan terdahulu', tables: ['persetujuan_warek3', 'pengajuan_kegiatan'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'rektor', name: '07 - Rektor', actor: 'Rektor',
    menus: [
      { decision: 'Menu Persetujuan?', action: 'Pilih pengajuan dan keputusan akhir', process: 'Simpan keputusan dan status akhir', tables: ['pengajuan_kegiatan', 'persetujuan_rektor', 'notifikasi', 'log_aktivitas'] },
      { decision: 'Daftar pengajuan?', action: 'Cari, filter, dan pilih pengajuan', process: 'Muat detail dan seluruh riwayat pemeriksaan', tables: ['pengajuan_kegiatan', 'verifikasi_dosen', 'persetujuan_dekan', 'verifikasi_bauak', 'persetujuan_warek3'] },
      { decision: 'Riwayat persetujuan?', action: 'Pilih riwayat', process: 'Muat keputusan akhir terdahulu', tables: ['persetujuan_rektor', 'pengajuan_kegiatan'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'admin', name: '08 - Admin', actor: 'Admin',
    menus: [
      { decision: 'Kelola Ormawa?', action: 'Tambah, ubah, atau hapus Ormawa', process: 'Validasi dan simpan data Ormawa', tables: ['ormawa', 'anggota_ormawa', 'users', 'log_aktivitas'] },
      { decision: 'Kelola Fakultas?', action: 'Tambah, ubah, atau hapus fakultas', process: 'Validasi dan simpan data fakultas', tables: ['fakultas', 'log_aktivitas'] },
      { decision: 'Kelola Dekan?', action: 'Tambah, ubah, atau hapus Dekan', process: 'Validasi dan simpan akun Dekan', tables: ['users', 'fakultas', 'log_aktivitas'] },
      { decision: 'Kelola Mahasiswa?', action: 'Tambah, ubah, atau hapus Mahasiswa', process: 'Validasi dan simpan akun Mahasiswa', tables: ['users', 'anggota_ormawa', 'log_aktivitas'] },
      { decision: 'Kelola pengajuan?', action: 'Cari, lihat, ubah, cetak, atau ekspor', process: 'Muat dan olah data pengajuan', tables: ['pengajuan_kegiatan', 'proposal', 'rab', 'log_aktivitas'] },
      { decision: 'Menu Laporan?', action: 'Pilih filter laporan', process: 'Susun statistik dan laporan sistem', tables: ['pengajuan_kegiatan', 'ormawa', 'fakultas', 'log_aktivitas'] },
    ],
  }),
  buildRolePage({
    id: 'pp', name: '09 - Pimpinan Pesantren', actor: 'Pimpinan Pesantren',
    menus: [
      { decision: 'Monitoring pengajuan?', action: 'Pilih pengajuan terbaru', process: 'Muat detail dan status pengajuan', tables: ['pengajuan_kegiatan', 'ormawa', 'log_aktivitas'] },
      { decision: 'Statistik bulanan?', action: 'Pilih periode statistik', process: 'Hitung perkembangan pengajuan', tables: ['pengajuan_kegiatan', 'log_aktivitas'] },
      { decision: 'Menu Profil?', action: 'Ubah profil dan kata sandi', process: 'Validasi dan simpan perubahan profil', tables: ['users', 'log_aktivitas'] },
      { decision: 'Menu Notifikasi?', action: 'Pilih notifikasi', process: 'Tampilkan dan tandai telah dibaca', tables: ['notifikasi', 'log_aktivitas'] },
    ],
  }),
];

const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<mxfile host="app.diagrams.net" modified="${now}" agent="Codex" version="24.7.17" type="device" compressed="false">${pages.join('')}</mxfile>\n`;
fs.writeFileSync(output, xml, 'utf8');
console.log(`Generated ${output.pathname} with ${pages.length} pages.`);
