<x-app-layout>
    <x-slot name="title">{{ $role->exists ? 'Edit Role' : 'Tambah Role' }}</x-slot>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <a href="{{ route('admin.rbac.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-arrow-left"></i>
                </a>
                {{ $role->exists ? 'Edit Role' : 'Tambah Role' }}
            </h2>
            <p class="text-[12px] text-gray-500 mt-1">Atur identitas role dan permission yang dimiliki.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-lg bg-danger-light text-danger text-[13px]">
            Periksa kembali input yang masih belum valid.
        </div>
    @endif

    <form action="{{ $role->exists ? route('admin.rbac.roles.update', $role) : route('admin.rbac.roles.store') }}" method="POST">
        @csrf
        @if($role->exists)
            @method('PATCH')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden h-fit">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-[15px] font-semibold text-gray-900">Informasi Role</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label for="name" class="block text-[13px] font-medium text-gray-700">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors" required>
                        @error('name') <p class="text-danger text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="slug" class="block text-[13px] font-medium text-gray-700">Slug</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $role->slug) }}" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors disabled:opacity-60" placeholder="otomatis dari nama" @disabled($role->is_system)>
                        <p class="text-[11px] text-gray-500">Slug role sistem dikunci agar kompatibel dengan route lama.</p>
                        @error('slug') <p class="text-danger text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="description" class="block text-[13px] font-medium text-gray-700">Deskripsi</label>
                        <textarea id="description" name="description" rows="4" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors">{{ old('description', $role->description) }}</textarea>
                        @error('description') <p class="text-danger text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-[13px] text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-brand focus:ring-brand" @checked(old('is_active', $role->is_active)) @disabled($role->is_system)>
                        Role aktif dan dapat ditetapkan ke user
                    </label>
                    @if($role->is_system)
                        <p class="text-[11px] text-warning">Role bawaan sistem selalu aktif dan tidak dapat diubah slug-nya.</p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Permission</h3>
                        <p class="text-[12px] text-gray-400">Centang permission yang boleh dilakukan role ini.</p>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    @foreach($permissions as $group => $items)
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                <h4 class="text-[13px] font-semibold text-gray-800">{{ $group }}</h4>
                                <span class="text-[11px] text-gray-400">{{ $items->count() }} permission</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4">
                                @foreach($items as $permission)
                                    <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:border-brand/30 hover:bg-gray-50 transition cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="mt-0.5 rounded border-gray-300 text-brand focus:ring-brand" @checked(in_array($permission->id, old('permissions', $selectedPermissions)))>
                                        <span>
                                            <span class="block text-[13px] font-semibold text-gray-800">{{ $permission->name }}</span>
                                            <span class="block text-[11px] text-gray-400 font-mono">{{ $permission->slug }}</span>
                                            @if($permission->description)
                                                <span class="block text-[11px] text-gray-500 mt-1">{{ $permission->description }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.rbac.index') }}" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">Batal</a>
                    <button type="submit" class="px-4 py-2 text-[13px] font-medium text-white bg-brand rounded-lg hover:bg-brand-active transition-colors shadow-sm flex items-center gap-2">
                        <i class="ti ti-device-floppy"></i> Simpan Role
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
