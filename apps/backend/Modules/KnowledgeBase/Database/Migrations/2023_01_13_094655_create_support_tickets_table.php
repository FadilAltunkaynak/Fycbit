<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('unique_code')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->text('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('purchase_code')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('is_seen_by_user')->nullable();
            $table->unsignedBigInteger('assigned_agent_id')->nullable();
            $table->dateTime('last_conversation_time')->nullable();
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
        Schema::dropIfExists('support_tickets');
    }
}
