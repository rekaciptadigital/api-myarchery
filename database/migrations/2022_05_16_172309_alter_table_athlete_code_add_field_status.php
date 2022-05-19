<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAthleteCodeAddFieldStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_user_athlete_codes', function (Blueprint $table) {
            $table->smallInteger("status")->default(1)->comment("1 untuk aktif dan 0 untuk non aktif");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_user_athlete_codes', function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
}
