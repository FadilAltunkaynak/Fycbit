<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIcoTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ico_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('approved_id')->nullable();
            $table->tinyInteger('approved_status')->default(0);
            $table->bigInteger('form_id')->nullable();
            $table->string('base_coin')->nullable();
            $table->string('coin_type')->nullable();
            $table->string('token_name')->nullable();
            $table->string('network')->nullable();
            $table->string('wallet_address')->nullable();
            $table->string('contract_address')->nullable();
            $table->text('wallet_private_key')->nullable();
            $table->string('chain_id')->nullable();
            $table->string('chain_link')->nullable();
            $table->string('website_link')->nullable();
            $table->longText('details_rule')->nullable();
            $table->integer('decimal')->nullable();
            $table->integer('gas_limit')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_updated')->default(0);
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
        Schema::dropIfExists('ico_tokens');
    }
}
