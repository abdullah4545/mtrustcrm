<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate([
            'name' => 'database.backup.download',
            'guard_name' => 'web',
        ]);

        $superadmin = Role::where('name', 'superadmin')->where('guard_name', 'web')->first();
        if ($superadmin && !$superadmin->hasPermissionTo($permission)) {
            $superadmin->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::where('name', 'database.backup.download')->where('guard_name', 'web')->first();
        if ($permission) $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
