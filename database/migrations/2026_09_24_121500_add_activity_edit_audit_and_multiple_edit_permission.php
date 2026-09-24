<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'edit_count')) {
                $table->unsignedInteger('edit_count')->default(0)->after('review_note');
            }
            if (!Schema::hasColumn('activities', 'last_edited_by')) {
                $table->unsignedBigInteger('last_edited_by')->nullable()->after('edit_count');
            }
            if (!Schema::hasColumn('activities', 'last_edited_at')) {
                $table->timestamp('last_edited_at')->nullable()->after('last_edited_by');
            }
        });

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->updateOrInsert(
                ['name' => 'activity.multiple_edit', 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );

            // Safe defaults: Super Admin and Admin may edit repeatedly.
            // Any other role can be granted this permission from Role & Permission.
            if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                $permissionId = DB::table('permissions')->where('name', 'activity.multiple_edit')->where('guard_name', 'web')->value('id');
                $roleIds = DB::table('roles')->whereIn('name', ['superadmin', 'admin'])->where('guard_name', 'web')->pluck('id');
                foreach ($roleIds as $roleId) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ], []);
                }
            }

            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }
        }
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['edit_count', 'last_edited_by', 'last_edited_at'],
                fn ($column) => Schema::hasColumn('activities', $column)
            ));
            if ($columns) $table->dropColumn($columns);
        });

        if (Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')->where('name', 'activity.multiple_edit')->where('guard_name', 'web')->value('id');
            if ($permissionId && Schema::hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            }
            if ($permissionId && Schema::hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            }
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
