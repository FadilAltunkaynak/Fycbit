<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminWalletKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_wallet_keys', function (Blueprint $table) {
            $table->id();
            $table->string("uid");
            $table->unsignedInteger("network_id")->unique();
            $table->string("address");
            $table->string("pv");
            $table->tinyInteger("creation_type")->default(1);
            $table->tinyInteger("status")->default(1);
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
        Schema::dropIfExists('admin_wallet_keys');
    }
}
