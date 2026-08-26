<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('quotations', function (Blueprint $table) {
      $table->id();

      $table->string('quotation_no', 60)->unique();

      $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate();
      $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();

      $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
      $table->foreignId('organization_contact_id')->nullable()->constrained('organization_contacts')->nullOnDelete();

      // Customer snapshot
      $table->string('client_name', 150)->nullable();
      $table->string('client_phone', 30)->nullable();
      $table->string('client_email', 150)->nullable();
      $table->string('client_address', 255)->nullable();

      $table->date('issue_date')->nullable();
      $table->date('valid_until')->nullable();

      $table->string('currency', 10)->default('BDT');
      $table->string('calculate_tax', 30)->default('after_discount');

      $table->longText('description')->nullable(); // editor text
      $table->text('note_for_recipient')->nullable();
      $table->text('terms')->nullable();

      $table->boolean('require_signature')->default(false);

      // totals
      $table->decimal('sub_total', 12, 2)->default(0);
      $table->decimal('discount_amount', 12, 2)->default(0);
      $table->decimal('tax_amount', 12, 2)->default(0);
      $table->decimal('grand_total', 12, 2)->default(0);

      // status via StatusStage (is_for='quotation')
      $table->foreignId('status_stage_id')->nullable()->constrained('status_stages')->nullOnDelete();

      $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['branch_id','lead_id','organization_id']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('quotations');
  }
};