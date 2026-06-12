<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnToIcoTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ico_tokens', function (Blueprint $table) {
            $table->string('image_name')->after('is_updated');
            $table->string('image_path')->after('image_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ico_tokens', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('image_path');
        });
    }
}
