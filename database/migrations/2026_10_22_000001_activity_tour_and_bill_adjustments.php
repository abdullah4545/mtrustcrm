<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  Schema::table('activities',function(Blueprint $t){$t->string('tour_type',20)->default('local_tour')->index();$t->dateTime('tour_ended_at')->nullable();$t->unsignedBigInteger('tour_ended_by')->nullable();});
  Schema::table('activity_travels',fn(Blueprint $t)=>$t->text('note')->nullable());
  Schema::table('activity_bill_histories',fn(Blueprint $t)=>$t->json('entry_snapshot')->nullable());
  DB::table('activities')->whereIn('id',DB::table('activity_travels')->select('activity_id')->where('tour_type','tour'))->update(['tour_type'=>'tour']);
  DB::table('permissions')->updateOrInsert(['name'=>'activity.tour.manage','guard_name'=>'web'],['created_at'=>now(),'updated_at'=>now()]);
  $permission=DB::table('permissions')->where('name','activity.tour.manage')->where('guard_name','web')->value('id');
  foreach(DB::table('roles')->where('name','superadmin')->pluck('id') as $role) DB::table('role_has_permissions')->updateOrInsert(['permission_id'=>$permission,'role_id'=>$role],[]);
  app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
 }
 public function down(): void {
  Schema::table('activity_bill_histories',fn(Blueprint $t)=>$t->dropColumn('entry_snapshot'));
  Schema::table('activity_travels',fn(Blueprint $t)=>$t->dropColumn('note'));
  Schema::table('activities',fn(Blueprint $t)=>$t->dropColumn(['tour_type','tour_ended_at','tour_ended_by']));
 }
};
