<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('map_location_link', 1000)->nullable()->after('website');
        });

        Schema::table('activity_travels', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->after('cost');
        });

        Schema::table('activity_expenses', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->after('note');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('subject', 500)->nullable();
            $table->string('salutation', 150)->nullable();
            $table->longText('cover_letter')->nullable();
            $table->longText('closing_note')->nullable();
            $table->string('terms_title', 150)->nullable();
            $table->string('sign_off', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['subject','salutation','cover_letter','closing_note','terms_title','sign_off']);
        });

        Schema::table('activity_expenses', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });

        Schema::table('activity_travels', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('map_location_link');
        });
    }
};
