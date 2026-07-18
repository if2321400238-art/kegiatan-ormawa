<x-app-layout>
    <x-slot name="title">Kelola RBAC</x-slot>

    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <h2 class="text-lg font-semibold text-gray-900">Role Based Access Control</h2>
                <p class="text-[12px] text-gray-500">Kelola role, permission, dan hak akses pengguna dari satu tempat.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.rbac.users') }}" class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                    <i class="ti ti-users"></i> Atur User
                </a>
                <a href="{{ route('admin.rbac.roles.create') }}" class="w-full sm:w-auto px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                    <i class="ti ti-plus"></i> Tambah Role
                </a>
            </div>
        </div>

        <div class="summary-stats">
            <div class="summary-stat-card" style="--accent: #3B82F6">
                <div class="text-[20px] font-bold text-gray-900">{{ $stats['roles'] }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Total Role</div>
            </div>
            <div class="summary-stat-card" style="--accent: #10B981">
                <div class="text-[20px] font-bold text-gray-900">{{ $stats['active_roles'] }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Role Aktif</div>
            </div>
            <div class="summary-stat-card" style="--accent: #8B5CF6">
                <div class="text-[20px] font-bold text-gray-900">{{ $stats['permissions'] }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Permission</div>
            </div>
            <div class="summary-stat-card" style="--accent: #F59E0B">
                <div class="text-[20px] font-bold text-gray-900">{{ $stats['users_with_multiple_roles'] }}</div>
                <div class="text-[11px] text-gray-500 font-medium">User Multi Role</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-light text-success text-[13px]">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-danger-light text-danger text-[13px]">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 table-card overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-semibold text-gray-900">Daftar Role</h3>
                    <p class="text-[12px] text-gray-400">Role sistem dan role custom yang dapat ditetapkan ke user.</p>
                </div>
                <span class="badge badge-info">{{ $roles->total() }} role</span>
            </div>

            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Permission</th>
                            <th>User</th>
                            <th>Status</th>
                            <th style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>
                                    <div class="font-semibold text-gray-900">{{ $role->name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $role->slug }}</div>
                                    @if($role->description)
                                        <div class="text-[12px] text-gray-500 mt-1 max-w-xs">{{ $role->description }}</div>
                                    @endif
                                </td>
                                <td><span class="badge badge-gray">{{ $role->permissions_count }} izin</span></td>
                                <td><span class="badge badge-info">{{ $role->users_count }} user</span></td>
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="badge {{ $role->is_active ? 'badge-success' : 'badge-danger' }}">{{ $role->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        @if($role->is_system)
                                            <span class="badge badge-warning">Sistem</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.rbac.roles.edit', $role) }}" class="p-1.5 bg-warning-light text-warning rounded-md hover:bg-warning hover:text-white transition-colors" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.rbac.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Hapus role ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-danger-light text-danger rounded-md hover:bg-danger hover:text-white transition-colors disabled:opacity-40" title="Hapus" @disabled($role->is_system)>
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400 text-sm">Belum ada role.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $roles->links('pagination::tailwind') }}
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="text-[15px] font-semibold text-gray-900">Katalog Permission</h3>
                <p class="text-[12px] text-gray-400">Permission bawaan yang tersedia untuk role.</p>
            </div>
            <div class="p-4 space-y-4 max-h-[720px] overflow-y-auto">
                @foreach($permissions as $group => $items)
                    <div>
                        <h4 class="text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-2">{{ $group }}</h4>
                        <div class="space-y-2">
                            @foreach($items as $permission)
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="text-[13px] font-semibold text-gray-800">{{ $permission->name }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">{{ $permission->slug }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
