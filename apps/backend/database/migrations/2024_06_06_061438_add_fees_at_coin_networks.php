<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeesAtCoinNetworks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_networks', function (Blueprint $table) {
            $table->decimal('withdrawal_fees', 29, 18)->default(0.0000001);
            $table->tinyInteger('withdrawal_fees_type')->default(2)->after('withdrawal_fees');
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
            //
        });
    }
}
