<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function create(): View
    {
        return view('admin.permissions.form', [
            'permission' => new Permission,
        ]);
    }

    public function store(PermissionRequest $request): RedirectResponse
    {
        Permission::query()->create($request->validated());

        return redirect()->route('admin.roles')->with('success', 'Permission added.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.form', [
            'permission' => $permission,
        ]);
    }

    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        $previousName = $permission->permission_name;
        $permission->update($request->validated());

        if ($previousName !== $permission->permission_name) {
            Role::query()->get()->each(function (Role $role) use ($previousName, $permission): void {
                $names = $role->permissions ?? [];
                $role->permissions = array_map(
                    fn (string $name): string => $name === $previousName ? $permission->permission_name : $name,
                    $names,
                );
                $role->save();
            });
        }

        return redirect()->route('admin.roles')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $used = Role::query()->get()->contains(
            fn (Role $role): bool => in_array($permission->permission_name, $role->permissions ?? [], true),
        );

        if ($used) {
            return back()->with('error', 'This permission is assigned to a role and cannot be deleted.');
        }

        $permission->delete();

        return redirect()->route('admin.roles')->with('success', 'Permission deleted.');
    }
}
