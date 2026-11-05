<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach (['catalogue.view','catalogue.manage','product_category.manage','product.category.manage'] as $name) {
            DB::table('permissions')->updateOrInsert(['name'=>$name,'guard_name'=>'web'], ['updated_at'=>now(),'created_at'=>now()]);
        }
        $viewId=DB::table('permissions')->where('name','catalogue.view')->where('guard_name','web')->value('id');
        foreach (DB::table('roles')->where('guard_name','web')->pluck('id') as $roleId) {
            DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$viewId,'role_id'=>$roleId], []);
        }
        // Users without a role also receive the default view permission.
        $model=(new \App\Models\User())->getMorphClass();
        foreach (DB::table('users')->whereNotIn('id',DB::table('model_has_roles')->where('model_type',$model)->select('model_id'))->pluck('id') as $userId) {
            DB::table('model_has_permissions')->updateOrInsert(['permission_id'=>$viewId,'model_type'=>$model,'model_id'=>$userId], []);
        }
        $permissionIds=DB::table('permissions')->whereIn('name',['catalogue.view','catalogue.manage','product_category.manage','product.category.manage'])->where('guard_name','web')->pluck('id');
        foreach (DB::table('roles')->where('name','superadmin')->where('guard_name','web')->pluck('id') as $roleId) {
            foreach ($permissionIds as $id) DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$id,'role_id'=>$roleId], []);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        // Preserve assigned role permissions on rollback.
    }
};
