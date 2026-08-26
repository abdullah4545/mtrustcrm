<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();

            $table->string('activity_type', 30); // call/visit/message/meeting/note
            $table->text('activity_text')->nullable();
            $table->dateTime('activity_at')->nullable();

            $table->string('outcome_status', 50)->nullable(); // interested/no_answer...
            $table->dateTime('next_followup_at')->nullable();
            $table->string('next_action_type', 30)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['lead_id','activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};