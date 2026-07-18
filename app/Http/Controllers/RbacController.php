<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RbacController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate(10);

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        $stats = [
            'roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'permissions' => Permission::count(),
            'users_with_multiple_roles' => DB::table('role_user')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
        ];

        return view('admin.rbac.index', compact('roles', 'permissions', 'stats'));
    }

    public function create(): View
    {
        $role = new Role(['is_active' => true]);
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $selectedPermissions = [];

        return view('admin.rbac.form', compact('role', 'permissions', 'selectedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRole($request);
        $slug = Str::slug($validated['slug'] ?: $validated['name']);

        if (Role::where('slug', $slug)->exists()) {
            return back()->withErrors(['slug' => 'Slug role sudah digunakan.'])->withInput();
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'is_active' => $request->boolean('is_active'),
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.rbac.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $selectedPermissions = $role->permissions()->pluck('permissions.id')->all();

        return view('admin.rbac.form', compact('role', 'permissions', 'selectedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $this->validateRole($request, $role);
        $slug = $role->is_system ? $role->slug : Str::slug($validated['slug'] ?: $validated['name']);

        if (! $role->is_system && Role::where('slug', $slug)->whereKeyNot($role->id)->exists()) {
            return back()->withErrors(['slug' => 'Slug role sudah digunakan.'])->withInput();
        }

        $role->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $role->is_system ? true : $request->boolean('is_active'),
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.rbac.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'Role bawaan sistem tidak dapat dihapus.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih digunakan oleh user. Lepaskan role dari user terlebih dahulu.');
        }

        $role->delete();

        return redirect()->route('admin.rbac.index')->with('success', 'Role berhasil dihapus.');
    }

    public function users(Request $request): View
    {
        $query = User::with(['roles' => fn ($q) => $q->orderBy('name')])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $request->integer('role_id')));
        }

        $users = $query->paginate(12)->withQueryString();
        $roles = Role::active()->orderBy('name')->get();

        return view('admin.rbac.users', compact('users', 'roles'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('is_active', true)],
        ]);

        $roleIds = $validated['roles'] ?? [];
        $adminRoleId = Role::where('slug', User::ROLE_ADMIN)->value('id');

        if ($user->is(auth()->user()) && $adminRoleId && ! in_array($adminRoleId, $roleIds)) {
            return back()->with('error', 'Tidak bisa menghapus role Administrator dari akun sendiri.');
        }

        $syncData = collect($roleIds)->mapWithKeys(fn ($roleId) => [
            $roleId => ['assigned_by' => auth()->id()],
        ])->all();

        $user->roles()->sync($syncData);

        $primaryRole = Role::whereIn('id', $roleIds)
            ->whereIn('slug', User::allowedRoles())
            ->orderByRaw("CASE slug WHEN 'admin' THEN 0 ELSE 1 END")
            ->value('slug');

        if ($primaryRole && $user->role !== $primaryRole) {
            $user->forceFill(['role' => $primaryRole])->save();
        }

        return back()->with('success', "Role untuk {$user->nama} berhasil diperbarui.");
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        $roleId = $role?->id;
        $slugRules = ['nullable', 'string', 'max:100'];

        if (! $role?->is_system) {
            $slugRules[] = Rule::unique('roles', 'slug')->ignore($roleId);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => $slugRules,
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
