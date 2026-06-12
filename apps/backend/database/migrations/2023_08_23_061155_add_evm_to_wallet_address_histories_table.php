<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvmToWalletAddressHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_address_histories', function (Blueprint $table) {
            $table->unsignedInteger('network_id')->nullable()->after("wallet_id");
            $table->unsignedInteger('coin_id')->nullable()->after("wallet_id");
            $table->tinyInteger('is_encrypted')->default(0)->after("wallet_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_address_histories', function (Blueprint $table) {
            $table->dropColumn('network_id');
            $table->dropColumn('coin_id');
            $table->dropColumn('is_encrypted');
        });
    }
}
