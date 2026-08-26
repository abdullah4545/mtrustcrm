<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');

            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_two')->nullable();
            $table->string('address')->nullable();
            $table->string('image_url')->nullable();
            $table->text('additional_info')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('designation_id')->nullable(); 
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('active'); // active/inactive
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            // ✅ avoid long index name issue: keep small index
            $table->index(['organization_id', 'status'], 'org_contact_org_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_contacts');
    }
};
