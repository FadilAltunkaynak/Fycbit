<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_five_minute_charts', function (Blueprint $table) {
            $this->cols($table);
        });

        Schema::create('future_fifteen_minute_charts', function (Blueprint $table) {
            $this->cols($table);
        });

        Schema::create('future_thirty_minute_charts', function (Blueprint $table) {
            $this->cols($table);
        });

        Schema::create('future_two_hour_charts', function (Blueprint $table) {
            $this->cols($table);
        });

        Schema::create('future_four_hour_charts', function (Blueprint $table) {
            $this->cols($table);
        });

        Schema::create('future_one_day_charts', function (Blueprint $table) {
            $this->cols($table);
        });
    }

    private function cols(Blueprint &$table)
    {
        $table->bigIncrements('id');
        $table->integer('interval')->unsigned();
        $table->bigInteger('base_coin_id');
        $table->bigInteger('trade_coin_id');
        $table->decimal('open',19,8);
        $table->decimal('close',19,8);
        $table->decimal('high',19,8);
        $table->decimal('low',19,8);
        $table->decimal('volume',19,8)->default(0);
        $table->unique(['base_coin_id', 'trade_coin_id','interval'], 'base_coin_id_trade_coin_id_interval');
        $table->timestamps();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('future_five_minute_charts');
        Schema::dropIfExists('future_fifteen_minute_charts');
        Schema::dropIfExists('future_thirty_minute_charts');
        Schema::dropIfExists('future_two_hour_charts');
        Schema::dropIfExists('future_four_hour_charts');
        Schema::dropIfExists('future_one_day_charts');
    }
}
