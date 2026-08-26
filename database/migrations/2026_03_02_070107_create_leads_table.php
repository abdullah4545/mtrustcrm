<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('lead_no', 50)->unique();

            // Branch access (branch-wise CRM)
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate();

            // Assigned user
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            //  Organization link
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();

            //  Organization contact person
            $table->foreignId('organization_contact_id')->nullable()->constrained('organization_contacts')->nullOnDelete();

            //  Lead Source (Platform)
            $table->foreignId('platform_id')->nullable()->constrained('platforms')->nullOnDelete();

            //  Lead Status from StatusStage (is_for=lead)
            $table->foreignId('status_stage_id')->nullable()->constrained('status_stages')->nullOnDelete();

            // Lead person (auto-fill from org contact OR manual custom)
            $table->string('person_name', 150)->nullable();
            $table->string('person_phone', 30)->nullable();
            $table->string('person_email', 150)->nullable();

            // optional
            $table->string('subject', 255)->nullable();
            $table->text('note')->nullable();
            $table->decimal('expected_value', 12, 2)->default(0);

            // followup snapshot (latest)
            $table->dateTime('next_followup_at')->nullable();
            $table->string('next_action_type', 30)->nullable(); // call/visit/message/meeting
            $table->dateTime('last_activity_at')->nullable();

            // open/closed
            $table->string('lead_state', 20)->default('open'); // open/closed
            $table->dateTime('closed_at')->nullable();
            $table->string('lost_reason', 255)->nullable();

            // audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id','assigned_user_id']);
            $table->index(['organization_id','organization_contact_id']);
            $table->index(['platform_id','status_stage_id']);
            $table->index(['person_phone']);
            $table->index(['next_followup_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};