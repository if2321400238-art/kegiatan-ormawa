@php
    $code = '413';
    $title = 'File yang diunggah terlalu besar';
    $message = 'Dokumen LPJ maksimal 10 MB, lampiran maksimal 5 MB per file, dan total unggahan dalam satu submit maksimal 32 MB. Kurangi ukuran file lalu coba lagi.';
    $icon = 'ti-file-alert';
    $iconBg = 'bg-danger-light text-danger';
@endphp
@include('errors.layout', compact('code', 'title', 'message', 'icon', 'iconBg'))
