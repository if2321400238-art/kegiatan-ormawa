@php
    $code = '500';
    $title = 'Terjadi gangguan pada sistem';
    $message = 'Permintaan belum bisa diproses. Silakan ulangi beberapa saat lagi atau hubungi admin jika masalah tetap muncul.';
    $icon = 'ti-alert-triangle';
    $iconBg = 'bg-danger-light text-danger';
@endphp
@include('errors.layout', compact('code', 'title', 'message', 'icon', 'iconBg'))
