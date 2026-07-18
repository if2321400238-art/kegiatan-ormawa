<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistem Ormawa') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-[var(--bg-page)]">
    <div x-data="{ sidebarOpen: false }"
        @keydown.escape.window="sidebarOpen = false"
        class="flex h-screen overflow-hidden bg-[var(--bg-page)]">

        <button type="button"
            x-show="sidebarOpen"
            x-transition.opacity
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-gray-950/50 lg:hidden"
            aria-label="Tutup menu"></button>

        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            @click="if ($event.target.closest('a')) sidebarOpen = false"
            class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-shrink-0 flex-col bg-brand transition-transform duration-300 lg:static lg:z-20 lg:translate-x-0">
            <!-- Logo -->
            <div class="px-6 py-6 border-b border-white/10 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center text-white text-xl">
                    <i class="ti ti-building-bank"></i>
                </div>
                <div>
                    <div class="text-[15px] font-bold text-white leading-tight">Sistem Ormawa</div>
                    <div class="text-[11px] text-white/50 font-medium">Univ. Nurul Jadid</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 flex flex-col gap-1">
                <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-2 pb-1">Menu Utama</div>

                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="ti ti-layout-dashboard"></i></div>
                    <span>Dashboard</span>
                </a>

                @canany(['ormawa.manage', 'akademik.manage', 'mahasiswa.manage', 'rbac.manage'])
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Master Data</div>
                    @can('ormawa.manage')
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.ormawa.index') }}" class="nav-item {{ request()->routeIs('admin.ormawa.*') ? 'active' : '' }}">
                                <div class="nav-icon"><i class="ti ti-building-community"></i></div>
                                <span>Kelola Ormawa</span>
                            </a>
                        @endif
                    @endcan
                    @can('akademik.manage')
                        <a href="{{ route('admin.akademik.index') }}" class="nav-item {{ request()->routeIs('admin.akademik.*','admin.fakultas.*','admin.dekan.*','admin.prodi.*','admin.kaprodi.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-school"></i></div>
                            <span>Kelola Akademik</span>
                        </a>
                    @endcan
                    @can('mahasiswa.manage')
                        <a href="{{ route('admin.mahasiswa.index') }}" class="nav-item {{ request()->routeIs('admin.mahasiswa.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-id-badge-2"></i></div>
                            <span>Mahasiswa Tersinkron</span>
                        </a>
                    @endcan
                    @can('rbac.manage')
                        <a href="{{ route('admin.rbac.index') }}" class="nav-item {{ request()->routeIs('admin.rbac.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-shield-lock"></i></div>
                            <span>Kelola RBAC</span>
                        </a>
                    @endcan
                @endcanany

                @canany(['pengajuan.view', 'lpj.view'])
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Kegiatan</div>
                    @can('pengajuan.view')
                        <a href="{{ route('pengajuan.index') }}" class="nav-item {{ request()->routeIs('pengajuan.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-file-description"></i></div>
                            <span>{{ auth()->user()->isMahasiswa() ? 'Kelola Pengajuan' : 'Pengajuan' }}</span>
                        </a>
                    @endcan
                    @can('lpj.view')
                        <a href="{{ route('lpj.index') }}" class="nav-item {{ request()->routeIs('lpj.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-report"></i></div><span>LPJ Kegiatan</span>
                        </a>
                    @endcan
                @endcanany

                @if (auth()->user()->isMahasiswa())
                    @php
                        $activeOrmawaSidebar = \App\Http\Controllers\MahasiswaDashboardController::getActiveOrmawa();
                        $jabatanSidebar = $activeOrmawaSidebar ? $activeOrmawaSidebar->anggota()->where('user_id', auth()->id())->first()?->jabatan : null;
                    @endphp
                    @if($activeOrmawaSidebar && in_array($jabatanSidebar, ['ketua', 'wakil_ketua']))
                        <a href="{{ route('ormawa.anggota.index', $activeOrmawaSidebar->id) }}" class="nav-item {{ request()->routeIs('ormawa.anggota.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-users"></i></div>
                            <span>Kelola Anggota</span>
                        </a>
                    @endif
                @endif

                @canany(['approval.bauak', 'lpj.verify', 'ormawa.manage'])
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Tugas Saya</div>
                    @can('approval.bauak')
                        <a href="{{ route('bauak.verifikasi.index') }}" class="nav-item {{ request()->routeIs('bauak.verifikasi.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-clipboard-check"></i></div>
                            <span>Verifikasi BAUAK</span>
                        </a>
                    @endcan
                    @can('lpj.verify')
                        <a href="{{ route('bauak.lpj.index') }}" class="nav-item {{ request()->routeIs('bauak.lpj.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="ti ti-report-search"></i></div><span>Verifikasi LPJ</span>
                        </a>
                    @endcan
                    @can('ormawa.manage')
                        @if (auth()->user()->isBauak())
                            <a href="{{ route('bauak.ormawa.index') }}" class="nav-item {{ request()->routeIs('bauak.ormawa.*') ? 'active' : '' }}">
                                <div class="nav-icon"><i class="ti ti-file-plus"></i></div>
                                <span>Data Ormawa</span>
                            </a>
                        @endif
                    @endcan
                @endcanany
                @can('approval.kaprodi')
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Tugas Saya</div>
                    <a href="{{ route('kaprodi.persetujuan.index') }}" class="nav-item {{ request()->routeIs('kaprodi.persetujuan.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-clipboard-check"></i></div>
                        <span>Persetujuan Kaprodi</span>
                    </a>
                    <a href="{{ route('kaprodi.ormawa.index') }}" class="nav-item {{ request()->routeIs('kaprodi.ormawa.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-users"></i></div>
                        <span>Ormawa Prodi</span>
                    </a>
                @endcan

                @can('approval.dekan')
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Tugas Saya</div>
                    <a href="{{ route('dekan.persetujuan.index') }}" class="nav-item {{ request()->routeIs('dekan.persetujuan.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-clipboard-check"></i></div>
                        <span>Persetujuan Dekan</span>
                    </a>
                    <a href="{{ route('dekan.ormawa.index') }}" class="nav-item {{ request()->routeIs('dekan.ormawa.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-school"></i></div>
                        <span>Daftar Ormawa</span>
                    </a>
                @endcan

                @can('approval.warek3')
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Persetujuan</div>
                    <a href="{{ route('warek3.persetujuan.index') }}" class="nav-item {{ request()->routeIs('warek3.persetujuan.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-file-description"></i></div>
                        <span>Persetujuan Warek 3</span>
                    </a>
                @endcan
                @can('approval.rektor')
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Persetujuan</div>
                    <a href="{{ route('rektor.persetujuan.index') }}" class="nav-item {{ request()->routeIs('rektor.persetujuan.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-clipboard-check   "></i></div>
                        <span>Persetujuan Rektor</span>
                    </a>

                @endcan

                @can('approval.pp')
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Persetujuan Akhir</div>
                    <a href="{{ route('pp.persetujuan.index') }}" class="nav-item {{ request()->routeIs('pp.persetujuan.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-clipboard-check"></i></div>
                        <span>Persetujuan Kepala PP</span>
                    </a>
                @endcan

                @can('lpj.view')
                    @if (auth()->user()->isAdmin())
                    <a href="{{ route('lpj.index') }}" class="nav-item {{ request()->routeIs('lpj.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-report-analytics"></i></div>
                        <span>Monitoring LPJ</span>
                    </a>
                    @endif
                @endcan

                {{-- User Settings Section in Sidebar for Mobile/Alternative --}}
                <div class="mt-auto">
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Sistem</div>
                    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="ti ti-settings"></i></div>
                        <span>Pengaturan Profil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-item w-full text-left">
                            <div class="nav-icon"><i class="ti ti-logout"></i></div>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 bg-[var(--bg-page)] relative z-10">

            <!-- TOPBAR -->
            <header class="min-h-16 bg-white border-b border-gray-200 flex items-center justify-between gap-2 px-3 sm:px-6 py-2 shadow-sm z-20 flex-shrink-0">
                <div class="flex min-w-0 items-center gap-2 sm:gap-4">
                    <button type="button"
                        @click="sidebarOpen = true"
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 lg:hidden"
                        aria-label="Buka menu">
                        <i class="ti ti-menu-2 text-xl"></i>
                    </button>

                    {{-- Breadcrumb / Title Area --}}
                    <div class="min-w-0">
                        <h1 class="truncate text-sm sm:text-base font-bold text-gray-900 m-0 leading-tight">
                            {{ $title ?? 'Dashboard' }}
                        </h1>
                        <span class="hidden truncate text-[11px] text-gray-500 font-medium sm:block">Sistem Ormawa / {{ $title ?? 'Dashboard' }}</span>
                    </div>
                </div>

                <div class="flex flex-shrink-0 items-center gap-1 sm:gap-3">
                    <!-- Notifications -->
                    @php
                        $notificationData = $notifikasi->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'link' => $item->link,
                                'dibaca' => $item->dibaca,
                            ];
                        })->toArray();
                    @endphp
                    <div x-data='{
                            openNotif: false,
                            notifications: @json($notificationData),
                            toggle() { this.openNotif = !this.openNotif; },
                            close() { this.openNotif = false; },
                            normalizeUrl(link) {
                                if (!link) return null;
                                try {
                                    const url = new URL(link, window.location.origin);
                                    return url.origin + url.pathname + url.search;
                                } catch (error) {
                                    return null;
                                }
                            },
                            currentPageUrl() {
                                return window.location.origin + window.location.pathname + window.location.search;
                            },
                            async markAsRead(notificationId) {
                                const token = document.querySelector("meta[name=\"csrf-token\"]")?.content;
                                if (!token || !notificationId) return;
                                try {
                                    await fetch(`/notifikasi/${notificationId}/read`, {
                                        method: "POST",
                                        headers: {
                                            "Accept": "application/json",
                                            "Content-Type": "application/json",
                                            "X-CSRF-TOKEN": token,
                                        },
                                        credentials: "same-origin",
                                        body: JSON.stringify({}),
                                    });
                                } catch (error) {
                                    console.error("Failed to mark notification as read", error);
                                }
                            },
                            async autoMarkCurrentPage() {
                                const currentUrl = this.currentPageUrl();
                                for (const notif of this.notifications || []) {
                                    if (!notif || notif.dibaca || !notif.link) continue;
                                    const notifUrl = this.normalizeUrl(notif.link);
                                    if (notifUrl && notifUrl === currentUrl) {
                                        await this.markAsRead(notif.id);
                                    }
                                }
                            }
                        }'
                        x-init="autoMarkCurrentPage()"
                        x-on:click.outside="close()"
                        class="relative inline-block">

                        <button type="button" @click.stop.prevent="toggle()"
                            class="relative p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition focus:outline-none">
                            <i class="ti ti-bell text-[20px]"></i>
                            @if ($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-4 px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <div x-show="openNotif"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            x-cloak
                            class="fixed left-3 right-3 top-16 z-50 mt-2 flex max-h-[calc(100vh-5rem)] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:w-[28rem] sm:max-w-[calc(100vw-2rem)] sm:origin-top-right">
                            @include('partials.notifikasi-dropdown')
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative sm:ml-2">
                        <button type="button" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-brand-accent/50 rounded-full" id="user-menu-button" onclick="toggleDropdown()">
                            <img class="w-9 h-9 rounded-full object-cover border border-gray-200 shadow-sm" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->nama }}">
                            <div class="hidden md:block text-left mr-1">
                                <div class="text-[13px] font-semibold text-gray-900 leading-tight">{{ auth()->user()->nama }}</div>
                                <div class="text-[11px] text-gray-500 font-medium leading-tight">{{ auth()->user()->role_label }}</div>
                            </div>
                            <i class="ti ti-chevron-down text-gray-400 text-sm hidden md:block"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-2 z-50 transform origin-top-right transition-all">
                            <div class="px-4 py-3 border-b border-gray-100 mb-1">
                                <p class="text-sm text-gray-900 font-semibold">{{ auth()->user()->nama }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50 hover:text-brand-active">
                                <i class="ti ti-user-circle text-lg"></i> Profil Saya
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50">
                                    <i class="ti ti-logout text-lg"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN SCROLLABLE CONTENT -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-3 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto w-full">

                    {{-- Alert Messages --}}
                    @if (session('success'))
                        <div class="mb-6 bg-success-light border border-success/20 text-emerald-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                            <i class="ti ti-circle-check-filled text-success text-xl"></i>
                            <span class="block text-[13px] font-medium pt-0.5">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="mb-6 bg-warning-light border border-warning/20 text-amber-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                            <i class="ti ti-alert-triangle-filled text-warning text-xl"></i>
                            <span class="block text-[13px] font-medium pt-0.5">{{ session('warning') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 bg-danger-light border border-danger/20 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                            <i class="ti ti-circle-x-filled text-danger text-xl"></i>
                            <span class="block text-[13px] font-medium pt-0.5">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Support both component slots and section-based views --}}
                    {!! $slot ?? '' !!}
                    @yield('content')

                </div>
            </main>

        </div>
    </div>

    <script>
        function toggleDropdown() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('#user-menu-button') && !event.target.closest('#user-menu-button')) {
                var dropdowns = document.getElementsByClassName("hidden absolute right-0"); // Simplified check
                var menu = document.getElementById('user-menu');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            }
        }
    </script>
</body>

</html>
