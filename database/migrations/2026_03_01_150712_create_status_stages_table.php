<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('status_stages', function (Blueprint $table) {
            $table->id();

            $table->string('name', 120);
            $table->string('color', 20)->default('#3b82f6');
            $table->string('is_for', 20); 
            $table->boolean('status')->default(true);  
            $table->timestamps();
 
            $table->unique(['is_for','name']);
            $table->index(['is_for','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_stages');
    }
};