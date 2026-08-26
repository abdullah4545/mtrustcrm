<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'accounts')->where('guard_name', 'web')->value('id');
        if (!$roleId) return;

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', ['activity.create', 'activity.edit'])
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ], []);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'accounts')->where('guard_name', 'web')->value('id');
        if (!$roleId) return;

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', ['activity.create', 'activity.edit'])
            ->pluck('id');

        DB::table('role_has_permissions')
            ->where('role_id', $roleId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }
};
