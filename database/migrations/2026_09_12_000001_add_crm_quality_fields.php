<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'existing_machine')) {
                $table->text('existing_machine')->nullable()->after('about_us');
            }
        });

        Schema::table('organization_contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_contacts', 'phone_numbers')) {
                $table->json('phone_numbers')->nullable()->after('phone_two');
            }
            if (!Schema::hasColumn('organization_contacts', 'email_addresses')) {
                $table->json('email_addresses')->nullable()->after('phone_numbers');
            }
        });

        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('status');
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                $table->string('review_note', 500)->nullable()->after('reviewed_at');
                $table->index(['status','branch_id','created_by'], 'activity_approval_scope_idx');
            }
        });

        if (Schema::hasTable('permissions')) {
            $permissionDefaults = [
                'activity.create_for_others' => ['superadmin','admin','manager','branch_manager'],
                'org.import' => ['superadmin','admin','manager'],
            ];

            foreach ($permissionDefaults as $permissionName => $roleNames) {
                DB::table('permissions')->updateOrInsert(
                    ['name'=>$permissionName,'guard_name'=>'web'],
                    ['updated_at'=>now(),'created_at'=>now()]
                );

                $permissionId = DB::table('permissions')->where('name',$permissionName)->where('guard_name','web')->value('id');
                if ($permissionId && Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                    $roleIds = DB::table('roles')->whereIn('name',$roleNames)->where('guard_name','web')->pluck('id');
                    foreach ($roleIds as $roleId) {
                        DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$permissionId,'role_id'=>$roleId],[]);
                    }
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
            if (Schema::hasColumn('activities', 'reviewed_by')) {
                $table->dropIndex('activity_approval_scope_idx');
                $table->dropColumn(['reviewed_by','reviewed_at','review_note']);
            }
        });
        Schema::table('organization_contacts', function (Blueprint $table) {
            $cols = array_values(array_filter(['phone_numbers','email_addresses'], fn($c) => Schema::hasColumn('organization_contacts',$c)));
            if ($cols) $table->dropColumn($cols);
        });
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations','existing_machine')) $table->dropColumn('existing_machine');
        });
    }
};
