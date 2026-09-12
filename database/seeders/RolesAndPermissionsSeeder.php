<?php

namespace Database\Seeders;

use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionCatalog::names() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $all = PermissionCatalog::names();

        Role::findOrCreate('Super Admin', 'web')->syncPermissions($all);

        Role::findOrCreate('Admin', 'web')->syncPermissions(PermissionCatalog::adminPermissions());

        Role::findOrCreate('Manager', 'web')->syncPermissions(PermissionCatalog::managerPermissions());

        Role::findOrCreate('Employee', 'web')->syncPermissions(PermissionCatalog::employeePermissions());
    }
}
