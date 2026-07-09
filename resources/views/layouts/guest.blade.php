<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Ormawa') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <main class="min-h-screen bg-[var(--bg-page)] lg:grid lg:grid-cols-[minmax(360px,42%)_1fr]">
            <section class="relative hidden overflow-hidden bg-brand text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 18% 18%, #ffffff 0 2px, transparent 2px), radial-gradient(circle at 82% 32%, #ffffff 0 2px, transparent 2px); background-size: 42px 42px;"></div>

                <div class="relative px-10 py-9">
                    <a href="/" class="inline-flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-2xl">
                            <i class="ti ti-building-bank"></i>
                        </span>
                        <span>
                            <span class="block text-[15px] font-bold leading-tight">Sistem Ormawa</span>
                            <span class="block text-[11px] font-medium text-white/60">Universitas Nurul Jadid</span>
                        </span>
                    </a>
                </div>

                <div class="relative px-10 pb-12">
                    <div class="mb-8 max-w-md">
                        <p class="mb-3 text-[12px] font-semibold uppercase tracking-widest text-white/55">Portal Kegiatan Mahasiswa</p>
                        <h1 class="text-4xl font-bold leading-tight tracking-normal">Pengajuan, verifikasi, dan pelaporan kegiatan dalam satu sistem.</h1>
                    </div>

                    <div class="grid max-w-md grid-cols-3 gap-3">
                        <div class="rounded-xl border border-white/10 bg-white/10 p-4">
                            <i class="ti ti-file-description text-xl text-white/80"></i>
                            <p class="mt-3 text-[12px] font-semibold text-white">Pengajuan</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/10 p-4">
                            <i class="ti ti-clipboard-check text-xl text-white/80"></i>
                            <p class="mt-3 text-[12px] font-semibold text-white">Verifikasi</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/10 p-4">
                            <i class="ti ti-report text-xl text-white/80"></i>
                            <p class="mt-3 text-[12px] font-semibold text-white">LPJ</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-10">
                <div class="w-full max-w-md">
                    <div class="mb-6 flex items-center justify-center gap-3 lg:hidden">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand text-2xl text-white">
                            <i class="ti ti-building-bank"></i>
                        </span>
                        <span>
                            <span class="block text-[15px] font-bold leading-tight text-gray-900">Sistem Ormawa</span>
                            <span class="block text-[11px] font-medium text-gray-500">Universitas Nurul Jadid</span>
                        </span>
                    </div>

                    <div class="overflow-hidden rounded-[20px] border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-accent">Akses Sistem</p>
                            <h2 class="mt-2 text-xl font-bold leading-tight text-gray-900">Masuk ke Sistem Ormawa</h2>
                        </div>

                        <div class="px-6 py-6 sm:px-8">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
