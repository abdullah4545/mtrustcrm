<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (Schema::hasTable('activities')) Schema::table('activities', function(Blueprint $t){
   if(!Schema::hasColumn('activities','payment_status')) $t->string('payment_status',30)->default('unpaid')->after('status')->index();
   if(!Schema::hasColumn('activities','paid_at')) $t->timestamp('paid_at')->nullable()->after('payment_status');
   if(!Schema::hasColumn('activities','paid_by')) $t->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
  });
  if (Schema::hasTable('activity_travels') && !Schema::hasColumn('activity_travels','tour_type')) Schema::table('activity_travels',fn(Blueprint $t)=>$t->string('tour_type',20)->default('local_tour')->after('activity_id'));
  $permissions=['profile.view','profile.update','profile.change_password','activity.bulk_review','activity.payment.manage'];
  foreach($permissions as $p) DB::table('permissions')->updateOrInsert(['name'=>$p,'guard_name'=>'web'],['created_at'=>now(),'updated_at'=>now()]);
  $allRoleIds=DB::table('roles')->pluck('id');
  foreach(['profile.view','profile.update','profile.change_password'] as $p){$pid=DB::table('permissions')->where('name',$p)->where('guard_name','web')->value('id'); foreach($allRoleIds as $rid) DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$pid,'role_id'=>$rid],[]);}
  $managerRoles=DB::table('roles')->whereIn('name',['superadmin','admin','manager','branch_manager'])->pluck('id');
  foreach(['activity.bulk_review','activity.payment.manage'] as $p){$pid=DB::table('permissions')->where('name',$p)->where('guard_name','web')->value('id'); foreach($managerRoles as $rid) DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$pid,'role_id'=>$rid],[]);}
 }
 public function down(): void {}
};
