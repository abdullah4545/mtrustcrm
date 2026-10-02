<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('expense_types') && !Schema::hasColumn('expense_types','bill_required')) {
            Schema::table('expense_types', fn(Blueprint $table) => $table->boolean('bill_required')->default(false)->after('status'));
        }
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->updateOrInsert(['name'=>'activity.status.view','guard_name'=>'web'], ['created_at'=>now(),'updated_at'=>now()]);
            $permissionId = DB::table('permissions')->where('name','activity.status.view')->where('guard_name','web')->value('id');
            if ($permissionId && Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                $roleIds = DB::table('roles')->whereIn('name',['superadmin','admin','manager','branch_manager'])->pluck('id');
                foreach ($roleIds as $roleId) DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$permissionId,'role_id'=>$roleId],[]);
            }
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            $id=DB::table('permissions')->where('name','activity.status.view')->where('guard_name','web')->value('id');
            if ($id && Schema::hasTable('role_has_permissions')) DB::table('role_has_permissions')->where('permission_id',$id)->delete();
            if ($id && Schema::hasTable('model_has_permissions')) DB::table('model_has_permissions')->where('permission_id',$id)->delete();
            if ($id) DB::table('permissions')->where('id',$id)->delete();
        }
        if (Schema::hasTable('expense_types') && Schema::hasColumn('expense_types','bill_required')) Schema::table('expense_types', fn(Blueprint $table)=>$table->dropColumn('bill_required'));
    }
};
