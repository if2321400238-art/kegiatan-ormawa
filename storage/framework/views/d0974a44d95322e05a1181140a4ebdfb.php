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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Verifikasi Pengajuan Kegiatan
            </h2>
            <a href="<?php echo e(route('bauak.verifikasi.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Kembali
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Informasi Kegiatan</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Judul Kegiatan</label>
                                    <p class="text-gray-900"><?php echo e($pengajuan->judul_kegiatan); ?></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tujuan</label>
                                    <p class="text-gray-900"><?php echo e($pengajuan->tujuan_kegiatan); ?></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tanggal</label>
                                        <p class="text-gray-900"><?php echo e($pengajuan->tanggal_mulai->format('d M Y')); ?> - <?php echo e($pengajuan->tanggal_selesai->format('d M Y')); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Lokasi</label>
                                        <p class="text-gray-900"><?php echo e($pengajuan->lokasi_kegiatan); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Dokumen</h3>
                            <div class="space-y-3">
                                <?php if($pengajuan->proposal): ?>
                                    <a href="<?php echo e($pengajuan->proposal->file_url); ?>" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                            </svg>
                                            <span class="ml-3 font-medium">Proposal Kegiatan</span>
                                        </div>
                                        <span class="text-blue-600">Lihat →</span>
                                    </a>
                                <?php endif; ?>

                                <?php if($pengajuan->rab): ?>
                                    <a href="<?php echo e($pengajuan->rab->file_url); ?>" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                            </svg>
                                            <span class="ml-3 font-medium">RAB</span>
                                        </div>
                                        <span class="text-blue-600">Lihat →</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Verifikasi</h3>

                            <form action="<?php echo e(route('bauak.verifikasi.verify', $pengajuan)); ?>" method="POST">
                                <?php echo csrf_field(); ?>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Keputusan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="">-- Pilih --</option>
                                        <option value="disetujui">✓ Setujui</option>
                                        <option value="revisi">⚠ Perlu Revisi</option>
                                        <option value="ditolak">✗ Tolak</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Catatan <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="catatan" required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Berikan catatan atau alasan..."></textarea>
                                </div>

                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Simpan Verifikasi
                                </button>
                            </form>
                        </div>
                    </div>

                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-gray-500 mb-3">ORGANISASI</h3>
                            <p class="text-lg font-semibold text-gray-900"><?php echo e($pengajuan->ormawa->nama_ormawa); ?></p>
                            <p class="text-sm text-gray-600 mt-1">Ketua: <?php echo e($pengajuan->ormawa->ketua); ?></p>
                        </div>
                    </div>
                </div>
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
<?php /**PATH /var/www/html/resources/views/bauak/verifikasi/show.blade.php ENDPATH**/ ?>