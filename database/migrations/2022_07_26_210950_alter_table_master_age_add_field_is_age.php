<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMasterAgeAddFieldIsAge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->smallInteger("is_age")->default(1)->comment("apakah event itu menggunakan usia atau tidak");
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
            $table->dropColumn("is_age");
        });
    }
}
