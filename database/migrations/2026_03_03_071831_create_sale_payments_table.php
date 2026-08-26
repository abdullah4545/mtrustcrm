<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sale_payments', function (Blueprint $table) {
      $table->id();

      $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
      $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate();

      $table->date('payment_date')->nullable();
      $table->decimal('amount', 12, 2)->default(0);

      $table->string('method', 30)->default('cash'); // cash,bkash,nagad,bank
      $table->string('transaction_ref', 120)->nullable();

      $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
      $table->text('note')->nullable();

      $table->timestamps();

      $table->index(['sale_id','branch_id']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('sale_payments');
  }
};