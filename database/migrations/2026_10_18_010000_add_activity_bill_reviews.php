<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('activities', function(Blueprint $t){
            $t->decimal('claimed_ta',14,2)->default(0);
            $t->decimal('claimed_da',14,2)->default(0);
            $t->decimal('claimed_total',14,2)->default(0);
            $t->decimal('deducted_total',14,2)->default(0);
        });
        DB::table('activities')->update(['claimed_ta'=>DB::raw('ta'),'claimed_da'=>DB::raw('da'),'claimed_total'=>DB::raw('total')]);
        DB::table('activities')->where('status','rejected')->update(['deducted_total'=>DB::raw('claimed_total'),'ta'=>0,'da'=>0,'total'=>0]);
        foreach(['activity_travels','activity_expenses'] as $table) Schema::table($table,function(Blueprint $t){
            $t->decimal('deduction_amount',14,2)->default(0);
            $t->string('bill_status',20)->default('submitted');
            $t->unsignedInteger('bill_review_version')->default(0);
            $t->text('deduction_reason')->nullable();
            $t->unsignedBigInteger('bill_reviewed_by')->nullable();
            $t->timestamp('bill_reviewed_at')->nullable();
        });
        Schema::create('activity_bill_histories',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('activity_id')->index();
            $t->string('entry_type',20);$t->unsignedBigInteger('entry_id');
            $t->string('entry_label',500);$t->decimal('claimed_amount',14,2);
            $t->decimal('old_deduction',14,2)->default(0);$t->decimal('new_deduction',14,2)->default(0);
            $t->string('old_status',20);$t->string('new_status',20);
            $t->text('reason');$t->unsignedBigInteger('reviewed_by')->nullable();$t->string('reviewer_name',150);
            $t->timestamp('created_at');
        });
    }
    public function down(): void {
        // Restore original claimed totals before dropping the deduction feature.
        DB::table('activities')->update(['ta'=>DB::raw('claimed_ta'),'da'=>DB::raw('claimed_da'),'total'=>DB::raw('claimed_total')]);
        Schema::dropIfExists('activity_bill_histories');
        foreach(['activity_travels','activity_expenses'] as $table) Schema::table($table,fn(Blueprint $t)=>$t->dropColumn(['deduction_amount','bill_status','bill_review_version','deduction_reason','bill_reviewed_by','bill_reviewed_at']));
        Schema::table('activities',fn(Blueprint $t)=>$t->dropColumn(['claimed_ta','claimed_da','claimed_total','deducted_total']));
    }
};
