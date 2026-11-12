<?php
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up(): void
    {
        $role = Role::findOrCreate('branch_manager', 'web');
        foreach (['activity.view_branch','activity.approve_own_branch','activity.status.view'] as $name) {
            $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }
        $remove = $role->permissions()->whereIn('name', ['activity.view_all','activity.approve','activity.bulk_review'])->get();
        foreach ($remove as $permission) $role->revokePermissionTo($permission);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        // Preserve existing roles and assignments.
    }
};
