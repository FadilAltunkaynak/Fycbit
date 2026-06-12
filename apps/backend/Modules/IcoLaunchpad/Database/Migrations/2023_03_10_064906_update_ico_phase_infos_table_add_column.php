<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateIcoPhaseInfosTableAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ico_phase_infos', function (Blueprint $table) {
            $table->decimal('minimum_purchase_price',$precision=19, $scale=8)->default(0);
            $table->decimal('maximum_purchase_price',$precision=19, $scale=8)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ico_phase_infos', function (Blueprint $table) {
            $table->dropColumn('minimum_purchase_price');
            $table->dropColumn('maximum_purchase_price');
        });
    }
}
