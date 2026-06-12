<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("g2f_enabled")->nullable()->change();
            $table->string("email_enabled")->nullable()->change();
            $table->string("phone_enabled")->nullable()->change();
        });

        Schema::table('permission_from_data', function (Blueprint $table) {
            $table->string("status")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
