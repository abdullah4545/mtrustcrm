<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sales', function (Blueprint $table) {
      $table->id();

      $table->string('sale_no', 60)->unique();
      $table->string('invoice_no', 60)->unique();

      $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate();

      $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
      $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();

      $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
      $table->foreignId('organization_contact_id')->nullable()->constrained('organization_contacts')->nullOnDelete();

      // snapshot
      $table->string('client_name', 150)->nullable();
      $table->string('client_phone', 30)->nullable();
      $table->string('client_email', 150)->nullable();
      $table->string('client_address', 255)->nullable();

      $table->foreignId('sold_by')->nullable()->constrained('users')->nullOnDelete();
      $table->date('sale_date')->nullable();

      // StatusStage (is_for='sales')
      $table->foreignId('status_stage_id')->nullable()->constrained('status_stages')->nullOnDelete();

      // totals
      $table->decimal('sub_total', 12, 2)->default(0);
      $table->decimal('discount_amount', 12, 2)->default(0);
      $table->decimal('tax_amount', 12, 2)->default(0);
      $table->decimal('grand_total', 12, 2)->default(0);

      // payment
      $table->decimal('paid_total', 12, 2)->default(0);
      $table->decimal('due_total', 12, 2)->default(0);
      $table->string('payment_status', 20)->default('unpaid');

      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->index(['branch_id','lead_id','quotation_id']);
      $table->index(['invoice_no','client_phone']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('sales');
  }
};