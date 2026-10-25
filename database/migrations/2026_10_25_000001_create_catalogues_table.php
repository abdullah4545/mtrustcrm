<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('catalogues')) {
            Schema::create('catalogues', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->unsignedBigInteger('category_id')->index();
                $table->longText('description')->nullable();
                $table->string('file_path', 500);
                $table->string('status', 20)->default('active')->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
        DB::table('permissions')->updateOrInsert(['name'=>'catalogue.manage','guard_name'=>'web'], ['created_at'=>now(),'updated_at'=>now()]);
        $permission = DB::table('permissions')->where('name','catalogue.manage')->where('guard_name','web')->value('id');
        foreach (DB::table('roles')->where('name','superadmin')->where('guard_name','web')->pluck('id') as $role) {
            DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$permission,'role_id'=>$role], []);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        Schema::dropIfExists('catalogues');
        $permission = \Spatie\Permission\Models\Permission::where('name','catalogue.manage')->where('guard_name','web')->first();
        if ($permission) $permission->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
