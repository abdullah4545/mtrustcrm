<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConvertedSaleIdToLeadsTable extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('converted_sale_id')
                  ->nullable()
                  ->after('id');

            // Foreign key (optional but recommended)
            $table->foreign('converted_sale_id')
                  ->references('id')
                  ->on('sales')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_sale_id']);
            $table->dropColumn('converted_sale_id');
        });
    }
}
