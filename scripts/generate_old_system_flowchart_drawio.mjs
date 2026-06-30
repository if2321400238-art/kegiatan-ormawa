import fs from 'node:fs';

const output = new URL('../docs/flowchart-sistem-lama-pengajuan-kegiatan.drawio', import.meta.url);
const esc = (value = '') => String(value)
  .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
  .replaceAll('>', '&gt;').replaceAll('"', '&quot;');
const cells = [];
let sequence = 1;
const id = (prefix) => `${prefix}-${sequence++}`;

function vertex(key, value, style, x, y, width, height) {
  const nodeId = id(key);
  cells.push(`<mxCell id="${nodeId}" value="${esc(value)}" style="${style}" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="${width}" height="${height}" as="geometry"/></mxCell>`);
  return nodeId;
}

function edge(source, target, label = '', options = {}) {
  const dashed = options.dashed ? 'dashed=1;dashPattern=6 4;' : '';
  const points = options.points?.length
    ? `<Array as="points">${options.points.map(([x, y]) => `<mxPoint x="${x}" y="${y}"/>`).join('')}</Array>` : '';
  cells.push(`<mxCell id="${id('edge')}" value="${esc(label)}" style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;endArrow=block;endFill=1;strokeColor=#222222;fontColor=#222222;strokeWidth=1.3;labelBackgroundColor=#ffffff;${dashed}" edge="1" parent="1" source="${source}" target="${target}"><mxGeometry relative="1" as="geometry">${points}</mxGeometry></mxCell>`);
}

const base = 'whiteSpace=wrap;html=1;strokeColor=#222222;fillColor=#ffffff;fontColor=#111111;fontFamily=Times New Roman;';
const style = {
  title: `${base}rounded=0;align=left;verticalAlign=middle;fontSize=19;fontStyle=1;spacingLeft=8;`,
  header: `${base}rounded=0;align=center;verticalAlign=middle;fontSize=13;fontStyle=1;`,
  lane: `${base}rounded=0;`,
  terminal: `${base}rounded=1;arcSize=45;fontSize=13;`,
  process: `${base}rounded=0;fontSize=12;`,
  input: `${base}shape=parallelogram;perimeter=parallelogramPerimeter;fontSize=12;`,
  decision: `${base}rhombus;fontSize=11;`,
  archive: `${base}shape=process;backgroundOutline=1;fontSize=12;`,
};

const left = 20;
const columnWidth = 240;
const headerY = 68;
const headerHeight = 72;
const laneHeight = 3050;
const roles = [
  'Mahasiswa / Ormawa', 'Dosen Pembina', 'Dekan<br>(Khusus Ormawa Fakultas)',
  'BAUAK<br>(Administrasi)', 'Wakil Rektor III', 'Rektor',
  'Kepala / Wakil PP', 'BAUAK<br>(Arsip dan Distribusi)',
];
const x = (column) => left + column * columnWidth;
const center = (column, width) => x(column) + (columnWidth - width) / 2;

vertex('title', 'Flowchart Sistem Lama Pengajuan dan Persetujuan Kegiatan Ormawa', style.title, left, 20, columnWidth * roles.length, 48);
roles.forEach((role, index) => {
  vertex('header', role, style.header, x(index), headerY, columnWidth, headerHeight);
  vertex('lane', '', style.lane, x(index), headerY + headerHeight, columnWidth, laneHeight);
});

const start = vertex('start', 'Mulai', style.terminal, center(0, 120), 175, 120, 48);
const proposal = vertex('proposal', 'Menyusun proposal dan RAB', style.process, center(0, 170), 270, 170, 66);
const consult = vertex('consult', 'Konsultasi dengan Dosen Pembina', style.process, center(0, 170), 380, 170, 66);

const dosenReview = vertex('dosen-review', 'Menelaah dan mengevaluasi proposal', style.process, center(1, 175), 500, 175, 70);
const dosenDecision = vertex('dosen-decision', 'Disetujui?', style.decision, center(1, 120), 610, 120, 95);
const dosenNote = vertex('dosen-note', 'Catatan revisi / penolakan', style.input, center(1, 175), 745, 175, 62);
const reviseDosen = vertex('revise-dosen', 'Memperbaiki proposal', style.process, center(0, 170), 745, 170, 62);

const level = vertex('level', 'Tingkat fakultas?', style.decision, center(2, 125), 850, 125, 98);
const dekanReview = vertex('dekan-review', 'Menelaah dan mengevaluasi proposal', style.process, center(2, 175), 990, 175, 70);
const dekanDecision = vertex('dekan-decision', 'Disetujui?', style.decision, center(2, 120), 1100, 120, 95);
const dekanNote = vertex('dekan-note', 'Catatan revisi / penolakan', style.input, center(2, 175), 1235, 175, 62);
const reviseDekan = vertex('revise-dekan', 'Memperbaiki proposal', style.process, center(0, 170), 1235, 170, 62);

const bauakReview = vertex('bauak-review', 'Verifikasi kelengkapan administrasi', style.process, center(3, 175), 1370, 175, 72);
const bauakDecision = vertex('bauak-decision', 'Berkas lengkap?', style.decision, center(3, 125), 1480, 125, 100);
const bauakNote = vertex('bauak-note', 'Meminta melengkapi berkas / menolak', style.input, center(3, 180), 1620, 180, 68);
const reviseBauak = vertex('revise-bauak', 'Melengkapi berkas', style.process, center(0, 170), 1620, 170, 62);

const warekReview = vertex('warek-review', 'Menelaah dan mengevaluasi proposal', style.process, center(4, 175), 1755, 175, 70);
const warekDecision = vertex('warek-decision', 'Disetujui?', style.decision, center(4, 120), 1865, 120, 95);
const warekNote = vertex('warek-note', 'Catatan / penolakan', style.input, center(4, 170), 2000, 170, 62);

const rektorReview = vertex('rektor-review', 'Menelaah dan mengevaluasi proposal', style.process, center(5, 175), 2135, 175, 70);
const rektorDecision = vertex('rektor-decision', 'Disetujui?', style.decision, center(5, 120), 2245, 120, 95);
const rektorNote = vertex('rektor-note', 'Catatan / penolakan', style.input, center(5, 170), 2380, 170, 62);

const ppReview = vertex('pp-review', 'Menelaah dan mengevaluasi proposal', style.process, center(6, 175), 2515, 175, 70);
const ppDecision = vertex('pp-decision', 'Disetujui?', style.decision, center(6, 120), 2625, 120, 95);
const ppNote = vertex('pp-note', 'Catatan / penolakan', style.input, center(6, 170), 2760, 170, 62);

const archive = vertex('archive', 'Mengarsipkan dan mendistribusikan hasil persetujuan', style.archive, center(7, 190), 2900, 190, 78);
const result = vertex('result', 'Menerima surat / hasil persetujuan', style.input, center(0, 180), 3030, 180, 68);
const finish = vertex('finish', 'Selesai', style.terminal, center(0, 120), 3140, 120, 48);

edge(start, proposal);
edge(proposal, consult);
edge(consult, dosenReview);
edge(dosenReview, dosenDecision);
edge(dosenDecision, level, 'Ya');
edge(dosenDecision, dosenNote, 'Tidak');
edge(dosenNote, reviseDosen, 'Revisi');
edge(reviseDosen, consult, 'Ajukan ulang', { dashed: true, points: [[210, 840], [210, 413]] });

edge(level, dekanReview, 'Ya');
edge(level, bauakReview, 'Tidak', { points: [[780, 930], [780, 1406]] });
edge(dekanReview, dekanDecision);
edge(dekanDecision, bauakReview, 'Ya');
edge(dekanDecision, dekanNote, 'Tidak');
edge(dekanNote, reviseDekan, 'Revisi');
edge(reviseDekan, consult, 'Ajukan ulang', { dashed: true, points: [[190, 1330], [190, 413]] });

edge(bauakReview, bauakDecision);
edge(bauakDecision, warekReview, 'Ya');
edge(bauakDecision, bauakNote, 'Tidak');
edge(bauakNote, reviseBauak, 'Lengkapi');
edge(reviseBauak, bauakReview, 'Ajukan ulang', { dashed: true, points: [[170, 1700], [890, 1700], [890, 1406]] });

edge(warekReview, warekDecision);
edge(warekDecision, rektorReview, 'Ya');
edge(warekDecision, warekNote, 'Tidak');
edge(rektorReview, rektorDecision);
edge(rektorDecision, ppReview, 'Ya');
edge(rektorDecision, rektorNote, 'Tidak');
edge(ppReview, ppDecision);
edge(ppDecision, archive, 'Ya');
edge(ppDecision, ppNote, 'Tidak');

edge(warekNote, result, 'Hasil', { dashed: true, points: [[1170, 2070], [1170, 3064]] });
edge(rektorNote, result, 'Hasil', { dashed: true, points: [[1410, 2450], [1410, 3064]] });
edge(ppNote, result, 'Hasil', { dashed: true, points: [[1650, 2830], [1650, 3064]] });
edge(archive, result);
edge(result, finish);

const xml = `<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" modified="${new Date().toISOString()}" agent="Codex" version="24.7.17" type="device" compressed="false">
  <diagram id="old-system" name="Banyak Role - Atas ke Bawah">
    <mxGraphModel dx="2100" dy="1000" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1960" pageHeight="3250" math="0" shadow="0">
      <root><mxCell id="0"/><mxCell id="1" parent="0"/>${cells.join('')}</root>
    </mxGraphModel>
  </diagram>
</mxfile>`;

fs.writeFileSync(output, xml);
console.log(output.pathname);
