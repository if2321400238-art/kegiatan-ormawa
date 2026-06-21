@component('mail::message')
# {{ $notifikasi->judul }}

{{ $notifikasi->pesan }}

@if ($notifikasi->link)
    @component('mail::button', ['url' => $notifikasi->link])
    Lihat Detail
    @endcomponent
@endif

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
