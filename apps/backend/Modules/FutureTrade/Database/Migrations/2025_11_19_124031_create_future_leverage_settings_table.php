<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureLeverageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_leverage_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique()->index();
            $table->uuid('coin_pair_uid')->index();
            $table->unsignedDecimal('min_position_amount', 29, 18)->default(0);
            $table->unsignedDecimal('max_position_amount', 29, 18)->default(0);
            $table->integer('max_leverage');
            $table->unsignedDecimal('maintenance_margin_rate', 8, 5)->default(0); // (0, )
            $table->unsignedDecimal('maintenance_amount', 29, 18);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('future_leverage_settings');
    }
}
