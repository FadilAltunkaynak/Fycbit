<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('unique_code')->nullable();
            $table->unsignedBigInteger('ticket_user_id')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('get_notification_by')->nullable();
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('support_notifications');
    }
}
