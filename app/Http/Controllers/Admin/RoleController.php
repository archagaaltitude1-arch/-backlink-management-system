<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $roles = Role::query()
            ->withCount('users')
            ->when($search !== '', fn ($query) => $query->where('role_name', 'like', "%{$search}%"))
            ->orderBy('role_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissions' => Permission::query()->orderBy('permission_name')->get(),
            'search' => $search,
            'roleCount' => Role::query()->count(),
            'permissionCount' => Permission::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role,
            'permissions' => Permission::query()->orderBy('permission_name')->get(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::query()->create([
            'role_name' => $request->validated('role_name'),
            'permissions' => array_values($request->validated('permissions') ?? []),
        ]);

        return redirect()->route('admin.roles.show', $role)->with('success', 'Role added.');
    }

    public function show(Role $role): View
    {
        $role->load(['users' => fn ($query) => $query->orderBy('name')]);
        $role->loadCount('users');

        return view('admin.roles.show', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('permission_name')->get(),
        ]);
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('permission_name')->get(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->role_name === 'Admin' && $request->validated('role_name') !== 'Admin') {
            return back()->withInput()->with('error', 'The Admin role name cannot be changed.');
        }

        $role->update([
            'role_name' => $request->validated('role_name'),
            'permissions' => array_values($request->validated('permissions') ?? []),
        ]);

        return redirect()->route('admin.roles.show', $role)->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->role_name === 'Admin') {
            return back()->with('error', 'The Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'This role is assigned to users and cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles')->with('success', 'Role deleted.');
    }
}
