<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvmToCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->integer("decimal")->default(18);
            $table->tinyInteger("sync_rate_status")->default(0);
            $table->tinyInteger("convert_status")->default(0);
            $table->decimal("min_convert_amount",29,18)->default(0);
            $table->decimal("max_convert_amount",29,18)->default(0);
            $table->tinyInteger("convert_fee_type")->default(1);
            $table->decimal("convert_fee",29,8)->default(0);
            $table->decimal("market_cap",29,18)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->dropColumn("decimal");
            $table->dropColumn("sync_rate_status");
            $table->dropColumn("convert_status");
            $table->dropColumn("min_convert_amount");
            $table->dropColumn("max_convert_amount");
            $table->dropColumn("convert_fee_type");
            $table->dropColumn("convert_fee");
            $table->dropColumn("market_cap");
        });
    }
}
