<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        $permissions = [
            'report.sales.view','report.leads.view','report.collections.view','report.activity.view',
            'report.view_all','report.view_branch','report.view_self',
        ];

        foreach ($permissions as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        $map = [
            'superadmin' => $permissions,
            'admin' => ['report.sales.view','report.leads.view','report.collections.view','report.activity.view','report.view_all'],
            'manager' => ['report.sales.view','report.leads.view','report.collections.view','report.activity.view','report.view_all'],
            'branch_manager' => ['report.sales.view','report.leads.view','report.collections.view','report.activity.view','report.view_branch'],
            'accounts' => ['report.sales.view','report.collections.view','report.activity.view','report.view_branch'],
            'staff' => ['report.sales.view','report.leads.view','report.collections.view','report.activity.view','report.view_self'],
        ];

        foreach ($map as $roleName => $names) {
            $roleId = DB::table('roles')->where('name',$roleName)->where('guard_name','web')->value('id');
            if (!$roleId) continue;
            foreach ($names as $name) {
                $permissionId = DB::table('permissions')->where('name',$name)->where('guard_name','web')->value('id');
                if ($permissionId) DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$permissionId,'role_id'=>$roleId],[]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name',[
            'report.sales.view','report.leads.view','report.collections.view','report.activity.view',
            'report.view_all','report.view_branch','report.view_self',
        ])->where('guard_name','web')->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id',$ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id',$ids)->delete();
        DB::table('permissions')->whereIn('id',$ids)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
