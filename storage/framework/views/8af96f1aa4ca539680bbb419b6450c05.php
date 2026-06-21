<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Sistem Ormawa')); ?></title>

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="font-sans antialiased text-gray-900 bg-[var(--bg-page)]">
    <div class="flex h-screen overflow-hidden bg-[var(--bg-page)]">

        <!-- SIDEBAR -->
        <aside class="w-[260px] bg-brand flex flex-col flex-shrink-0 transition-all duration-300 z-20">
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

                <a href="<?php echo e(route('dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <div class="nav-icon"><i class="ti ti-layout-dashboard"></i></div>
                    <span>Dashboard</span>
                </a>

                <?php if(auth()->user()->isAdmin()): ?>
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Master Data</div>
                    <a href="<?php echo e(route('admin.ormawa.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.ormawa.*') ? 'active' : ''); ?>">
                        <div class="nav-icon"><i class="ti ti-building-community"></i></div>
                        <span>Kelola Ormawa</span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->isOrmawa()): ?>
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Kegiatan</div>
                    <a href="<?php echo e(route('pengajuan.index')); ?>" class="nav-item <?php echo e(request()->routeIs('pengajuan.*') ? 'active' : ''); ?>">
                        <div class="nav-icon"><i class="ti ti-file-description"></i></div>
                        <span>Pengajuan</span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->isBauak()): ?>
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Tugas Saya</div>
                    <a href="<?php echo e(route('bauak.verifikasi.index')); ?>" class="nav-item <?php echo e(request()->routeIs('bauak.verifikasi.*') ? 'active' : ''); ?>">
                        <div class="nav-icon"><i class="ti ti-clipboard-check"></i></div>
                        <span>Verifikasi BAUAK</span>
                    </a>
                    <a href="<?php echo e(route('pengajuan.index')); ?>" class="nav-item <?php echo e(request()->routeIs('pengajuan.*') ? 'active' : ''); ?>">
                        <div class="nav-icon"><i class="ti ti-file-description"></i></div>
                        <span>Semua Pengajuan</span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->isWarek3()): ?>
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Persetujuan</div>
                    <a href="<?php echo e(route('warek3.persetujuan.index')); ?>" class="nav-item <?php echo e(request()->routeIs('warek3.persetujuan.*') ? 'active' : ''); ?>">
                        <div class="nav-icon"><i class="ti ti-stamp"></i></div>
                        <span>Persetujuan Warek 3</span>
                    </a>
                <?php endif; ?>

                
                <div class="mt-auto">
                    <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-4 pt-4 pb-1">Sistem</div>
                    <a href="<?php echo e(route('profile.edit')); ?>" class="nav-item <?php echo e(request()->routeIs('profile.edit') ? 'active' : ''); ?>">
                        <div class="nav-icon"><i class="ti ti-settings"></i></div>
                        <span>Pengaturan Profil</span>
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
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
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shadow-sm z-20 flex-shrink-0">
                <div class="flex items-center gap-4">
                    
                    <div>
                        <h1 class="text-base font-bold text-gray-900 m-0 leading-tight">
                            <?php echo e($title ?? 'Dashboard'); ?>

                        </h1>
                        <span class="text-[11px] text-gray-500 font-medium">Sistem Ormawa / <?php echo e($title ?? 'Dashboard'); ?></span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notifications -->
                    <?php
                        $notificationData = $notifikasi->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'link' => $item->link,
                                'dibaca' => $item->dibaca,
                            ];
                        })->toArray();
                    ?>
                    <div x-data='{
                            openNotif: false,
                            notifications: <?php echo json_encode($notificationData, 15, 512) ?>,
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
                            <?php if($unreadCount > 0): ?>
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-4 px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white">
                                    <?php echo e($unreadCount); ?>

                                </span>
                            <?php endif; ?>
                        </button>

                        <div x-show="openNotif"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            x-cloak
                            class="absolute right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 origin-top-right overflow-hidden flex flex-col"
                            style="display: none; width: 28rem; min-width: 28rem;">
                            <?php echo $__env->make('partials.notifikasi-dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative ml-2">
                        <button type="button" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-brand-accent/50 rounded-full" id="user-menu-button" onclick="toggleDropdown()">
                            <img class="w-9 h-9 rounded-full object-cover border border-gray-200 shadow-sm" src="<?php echo e(auth()->user()->avatar); ?>" alt="<?php echo e(auth()->user()->nama); ?>">
                            <div class="hidden md:block text-left mr-1">
                                <div class="text-[13px] font-semibold text-gray-900 leading-tight"><?php echo e(auth()->user()->nama); ?></div>
                                <div class="text-[11px] text-gray-500 font-medium leading-tight"><?php echo e(auth()->user()->role_label); ?></div>
                            </div>
                            <i class="ti ti-chevron-down text-gray-400 text-sm hidden md:block"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-2 z-50 transform origin-top-right transition-all">
                            <div class="px-4 py-3 border-b border-gray-100 mb-1">
                                <p class="text-sm text-gray-900 font-semibold"><?php echo e(auth()->user()->nama); ?></p>
                                <p class="text-xs text-gray-500 truncate"><?php echo e(auth()->user()->email); ?></p>
                            </div>
                            <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-2 px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50 hover:text-brand-active">
                                <i class="ti ti-user-circle text-lg"></i> Profil Saya
                            </a>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50">
                                    <i class="ti ti-logout text-lg"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN SCROLLABLE CONTENT -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto w-full">

                    
                    <?php if(session('success')): ?>
                        <div class="mb-6 bg-success-light border border-success/20 text-emerald-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                            <i class="ti ti-circle-check-filled text-success text-xl"></i>
                            <span class="block text-[13px] font-medium pt-0.5"><?php echo e(session('success')); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if(session('warning')): ?>
                        <div class="mb-6 bg-warning-light border border-warning/20 text-amber-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                            <i class="ti ti-alert-triangle-filled text-warning text-xl"></i>
                            <span class="block text-[13px] font-medium pt-0.5"><?php echo e(session('warning')); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="mb-6 bg-danger-light border border-danger/20 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                            <i class="ti ti-circle-x-filled text-danger text-xl"></i>
                            <span class="block text-[13px] font-medium pt-0.5"><?php echo e(session('error')); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php echo e($slot); ?>


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

<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>