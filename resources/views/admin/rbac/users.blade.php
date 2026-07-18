<x-app-layout>
    <x-slot name="title">Atur Role User</x-slot>

    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <a href="{{ route('admin.rbac.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                    Atur Role User
                </h2>
                <p class="text-[12px] text-gray-500">Tetapkan satu atau lebih role untuk setiap user.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-light text-success text-[13px]">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-danger-light text-danger text-[13px]">{{ session('error') }}</div>
    @endif

    <div class="table-card overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.rbac.users') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, email..." class="md:col-span-2 bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5">
                <select name="role_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5">
                    <option value="">Semua role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                    <i class="ti ti-search"></i> Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role Legacy</th>
                        <th>Role RBAC</th>
                        <th style="width: 340px;">Ubah Role</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $user->nama }}</div>
                                <div class="text-[12px] text-gray-500">{{ $user->username }} · {{ $user->email }}</div>
                                @unless($user->is_active)
                                    <span class="badge badge-danger mt-1">Nonaktif</span>
                                @endunless
                            </td>
                            <td><span class="badge badge-gray">{{ $user->role_label }}</span></td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <span class="badge {{ $role->slug === 'admin' ? 'badge-danger' : 'badge-info' }}">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-[12px] text-gray-400 italic">Belum ada role RBAC</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.rbac.users.update', $user) }}" method="POST" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-gray-50 rounded-lg border border-gray-100">
                                        @foreach($roles as $role)
                                            <label class="flex items-center gap-2 text-[12px] text-gray-700">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-gray-300 text-brand focus:ring-brand" @checked($user->roles->contains('id', $role->id))>
                                                <span>{{ $role->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="px-3 py-1.5 text-[12px] font-medium text-white bg-brand rounded-lg hover:bg-brand-active transition-colors shadow-sm flex items-center gap-1.5">
                                        <i class="ti ti-device-floppy"></i> Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-400 text-sm">User tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $users->links('pagination::tailwind') }}
        </div>
    </div>
</x-app-layout>
