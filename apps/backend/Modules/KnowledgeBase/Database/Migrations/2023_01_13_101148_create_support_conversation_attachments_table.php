<?php

use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportConversationAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_conversation_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('conversation_id')->nullable();
            $table->text('file_link')->nullable();
            $table->string('file_type')->nullable();
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
        Schema::dropIfExists('support_conversation_attachments');
    }
}
