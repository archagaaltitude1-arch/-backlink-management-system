<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the role and permission catalogue without duplicating names.
     */
    public function run(): void
    {
        $permissions = [
            ['permission_name' => 'manage-customers', 'description' => 'View and manage customer accounts.'],
            ['permission_name' => 'manage-staff', 'description' => 'View and manage staff accounts.'],
            ['permission_name' => 'manage-websites', 'description' => 'View and manage publisher websites.'],
            ['permission_name' => 'manage-services', 'description' => 'View and manage backlink services.'],
            ['permission_name' => 'manage-niches', 'description' => 'View and manage niches.'],
            ['permission_name' => 'manage-packages', 'description' => 'View and manage packages.'],
            ['permission_name' => 'manage-roles', 'description' => 'View and manage roles and permissions.'],
            ['permission_name' => 'view-reports', 'description' => 'View administrative reports.'],
            ['permission_name' => 'manage-settings', 'description' => 'Update administrative settings.'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['permission_name' => $permission['permission_name']],
                ['description' => $permission['description']],
            );
        }

        $all = Permission::query()->orderBy('permission_name')->pluck('permission_name')->all();

        $roles = [
            'Admin' => $all,
            'Staff' => [
                'manage-customers',
                'manage-websites',
                'manage-services',
                'view-reports',
            ],
            'Customer' => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            Role::query()->updateOrCreate(
                ['role_name' => $roleName],
                ['permissions' => $rolePermissions],
            );
        }
    }
}
