<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug',180)->unique();
            $table->string('description')->nullable();
            $table->integer('block_confirmation')->nullable();
            $table->tinyInteger('base_type');
            $table->string('rpc_url')->nullable();
            $table->string('wss_url')->nullable();
            $table->string('explorer_url')->nullable();
            $table->string('chain_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('logo')->nullable();
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
        Schema::dropIfExists('networks');
    }
}
