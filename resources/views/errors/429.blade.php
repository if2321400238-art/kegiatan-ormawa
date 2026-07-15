@php
    $code = '429';
    $title = 'Terlalu banyak permintaan';
    $message = 'Sistem menerima terlalu banyak aksi dalam waktu singkat. Tunggu sebentar lalu coba lagi.';
    $icon = 'ti-hourglass-high';
    $iconBg = 'bg-warning-light text-warning';
@endphp
@include('errors.layout', compact('code', 'title', 'message', 'icon', 'iconBg'))
