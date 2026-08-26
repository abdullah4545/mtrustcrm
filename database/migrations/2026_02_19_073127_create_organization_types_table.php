<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // ✅ short index names
            $table->index(['is_active'], 'ot_active_idx');
            $table->index(['created_at'], 'ot_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_types');
    }
};
