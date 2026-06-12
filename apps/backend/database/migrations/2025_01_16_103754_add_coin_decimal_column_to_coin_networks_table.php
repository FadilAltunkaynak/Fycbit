<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinDecimalColumnToCoinNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_networks', function (Blueprint $table) {
            $table->unsignedInteger('coin_decimal')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_networks', function (Blueprint $table) {
            $table->dropColumn('coin_decimal');
        });
    }
}
