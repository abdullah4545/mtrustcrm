<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('organization_id');
            $table->string('organization_name');
            $table->string('department')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('details')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->string('vehicle')->nullable();
            $table->text('work_details')->nullable();
            $table->decimal('ta', 10, 2)->default(0);
            $table->decimal('da', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('branch_id');
            $table->enum('status', ['pending','approved','rejected'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
