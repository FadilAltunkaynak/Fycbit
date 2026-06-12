<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWithdrawalAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_withdrawal_addresses', function (Blueprint $table) {
            $table->id();
            $table->string("uid");
            $table->unsignedInteger("user_id");
            $table->string("label")->nullable();
            $table->unsignedInteger("currency_id");
            $table->unsignedInteger("network_id");
            $table->string("address");
            $table->tinyInteger("is_universal")->default(1);
            $table->tinyInteger("status")->default(1);
            $table->string("memo")->nullable();
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
        Schema::dropIfExists('user_withdrawal_addresses');
    }
}
