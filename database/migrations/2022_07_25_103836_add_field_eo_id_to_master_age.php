<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldEoIdToMasterAge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->smallInteger("eo_id")->default(0)->comment("untuk nampung eo id yang buat umurnya");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->dropColumn("eo_id");
        });
    }
}
