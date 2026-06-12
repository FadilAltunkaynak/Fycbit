<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportedNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supported_networks', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->string('slug');
            $table->string('name');
            $table->tinyInteger('network_type')->default(1);
            $table->integer('chain_id')->default(0);
            $table->string('native_currency')->nullable();
            $table->string('base_url')->nullable();
            $table->string('token_endpoint')->nullable();
            $table->string('address_endpoint')->nullable();
            $table->string('tx_endpoint')->nullable();
            $table->string('gas_limit')->nullable();
            $table->string('gas_price')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('supported_networks');
    }
}
