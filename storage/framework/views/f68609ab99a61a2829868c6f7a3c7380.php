<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Dashboard BAUAK <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        
        <div class="stat-card" style="--accent: #F59E0B">
            <div class="stat-icon bg-warning-light text-warning">
                <i class="ti ti-clock-down"></i>
            </div>
            <div>
                <span class="stat-label">Menunggu Verifikasi</span>
                <span class="stat-value"><?php echo e($stats['menunggu_verifikasi'] ?? 0); ?></span>
            </div>
        </div>

        
        <div class="stat-card" style="--accent: #3B82F6">
            <div class="stat-icon bg-info-light text-info">
                <i class="ti ti-calendar-check"></i>
            </div>
            <div>
                <span class="stat-label">Diverifikasi Hari Ini</span>
                <span class="stat-value"><?php echo e($stats['diverifikasi_hari_ini'] ?? 0); ?></span>
            </div>
        </div>

        
        <div class="stat-card" style="--accent: #10B981">
            <div class="stat-icon bg-success-light text-success">
                <i class="ti ti-check"></i>
            </div>
            <div>
                <span class="stat-label">Total Disetujui</span>
                <span class="stat-value"><?php echo e($stats['total_disetujui'] ?? 0); ?></span>
            </div>
        </div>

        
        <div class="stat-card" style="--accent: #F97316">
            <div class="stat-icon bg-orange-light text-orange">
                <i class="ti ti-edit"></i>
            </div>
            <div>
                <span class="stat-label">Perlu Revisi</span>
                <span class="stat-value"><?php echo e($stats['perlu_revisi'] ?? 0); ?></span>
            </div>
        </div>

    </div>

    
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        
        
        <div class="flex flex-col gap-6 min-h-0">
            <div class="table-card flex-1">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Menunggu Verifikasi</h3>
                        <p class="text-[12px] text-gray-400">Pengajuan yang perlu ditinjau</p>
                    </div>
                    <a href="<?php echo e(route('bauak.verifikasi.index')); ?>" class="badge badge-warning hover:bg-warning-light/80">Lihat Semua</a>
                </div>
                
                <?php if(count($pengajuanMenunggu ?? []) > 0): ?>
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Ormawa & Kegiatan</th>
                                <th>Waktu Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $pengajuanMenunggu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900"><?php echo e($item->judul_kegiatan); ?></div>
                                    <div class="text-[11px] text-gray-500"><?php echo e($item->ormawa->nama_ormawa); ?></div>
                                </td>
                                <td>
                                    <div class="text-[12px]"><?php echo e($item->created_at->diffForHumans()); ?></div>
                                    <div class="text-[11px] text-gray-400"><?php echo e($item->created_at->format('d M Y, H:i')); ?></div>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('bauak.verifikasi.show', $item)); ?>" class="badge badge-info hover:underline text-xs">Verifikasi</a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="flex-1 flex flex-col items-center justify-center p-8 gap-3">
                    <div class="w-12 h-12 rounded-full bg-success-light flex items-center justify-center text-success text-2xl">
                        <i class="ti ti-check"></i>
                    </div>
                    <p class="text-sm text-gray-400">Semua pengajuan telah diverifikasi</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="flex flex-col gap-6 min-h-0">
            
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Riwayat Verifikasi Anda</h3>
                        <p class="text-[12px] text-gray-400">Pengajuan yang telah Anda proses</p>
                    </div>
                </div>
                <div class="p-4 overflow-y-auto flex-1">
                    <?php $__empty_1 = true; $__currentLoopData = $riwayatVerifikasi ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $verifikasi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-3 p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 text-[13px] leading-tight mb-1"><?php echo e($verifikasi->pengajuanKegiatan->judul_kegiatan); ?></h4>
                                    <p class="text-[11px] text-gray-500"><?php echo e($verifikasi->pengajuanKegiatan->ormawa->nama_ormawa); ?></p>
                                </div>
                                <span class="badge <?php echo e($verifikasi->status_badge === 'success' ? 'badge-success' : ($verifikasi->status_badge === 'danger' ? 'badge-danger' : 'badge-orange')); ?> flex-shrink-0">
                                    <?php echo e($verifikasi->status_label ?? 'Diverifikasi'); ?>

                                </span>
                            </div>
                            <?php if($verifikasi->catatan): ?>
                                <div class="text-[11px] text-gray-600 bg-white border border-gray-200 p-2 rounded-md mt-2">
                                    <span class="font-semibold text-gray-900">Catatan:</span> <?php echo e($verifikasi->catatan); ?>

                                </div>
                            <?php endif; ?>
                            <div class="text-[10px] text-gray-400 mt-2 flex items-center gap-1">
                                <i class="ti ti-clock"></i> <?php echo e($verifikasi->tanggal_verifikasi->format('d M Y, H:i')); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="py-12 flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 text-2xl">
                                <i class="ti ti-history"></i>
                            </div>
                            <div class="text-center text-gray-400 text-sm">Belum ada riwayat verifikasi</div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if(($riwayatVerifikasi ?? collect())->hasPages()): ?>
                <div class="p-4 border-t border-gray-100">
                    <?php echo e($riwayatVerifikasi->links()); ?>

                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/dashboard/bauak.blade.php ENDPATH**/ ?>