@php
    $code = '404';
    $title = 'Halaman tidak ditemukan';
    $message = 'Alamat yang dibuka tidak tersedia atau aksesnya sudah berubah.';
    $icon = 'ti-map-question';
    $iconBg = 'bg-info-light text-info';
@endphp
@include('errors.layout', compact('code', 'title', 'message', 'icon', 'iconBg'))
