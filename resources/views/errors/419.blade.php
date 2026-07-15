@php
    $code = '419';
    $title = 'Sesi sudah berakhir';
    $message = 'Halaman terlalu lama terbuka atau sesi login kedaluwarsa. Muat ulang halaman, login kembali jika diminta, lalu ulangi prosesnya.';
    $icon = 'ti-clock-exclamation';
    $iconBg = 'bg-warning-light text-warning';
@endphp
@include('errors.layout', compact('code', 'title', 'message', 'icon', 'iconBg'))
