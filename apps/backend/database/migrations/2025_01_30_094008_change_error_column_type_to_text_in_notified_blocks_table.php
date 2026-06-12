<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeErrorColumnTypeToTextInNotifiedBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notified_blocks', function (Blueprint $table) {
            $table->text('error')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notified_blocks', function (Blueprint $table) {
            $table->string('error')->change();
        });
    }
}
