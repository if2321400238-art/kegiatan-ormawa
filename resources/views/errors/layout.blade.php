<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Terjadi Kesalahan' }} - {{ config('app.name', 'Sistem Ormawa') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--bg-page)] font-sans text-gray-900">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <section class="w-full max-w-lg rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm sm:p-8">
            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl {{ $iconBg ?? 'bg-warning-light text-warning' }}">
                <i class="ti {{ $icon ?? 'ti-alert-triangle' }} text-3xl"></i>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $code ?? 'Error' }}</p>
            <h1 class="mt-2 text-xl font-bold leading-snug text-gray-900">{{ $title ?? 'Terjadi Kesalahan' }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $message ?? 'Sistem tidak dapat memproses permintaan saat ini.' }}</p>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-center">
                <button type="button" onclick="history.back()" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="ti ti-arrow-left"></i>
                    Kembali
                </button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-active">
                    <i class="ti ti-layout-dashboard"></i>
                    Dashboard
                </a>
            </div>
        </section>
    </main>
</body>
</html>
