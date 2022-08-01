<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddFieldMaxDateOfBirthAndMinDateOfBirthIntoTableMasterAgeCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->dateTime("min_date_of_birth")->nullable()->comment("tanggal lahir minimal user yang bisa daftar event");
            $table->dateTime("max_date_of_birth")->nullable()->comment("tanggal lahir maksimal user yang bisa daftar event");
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
            $table->dropColumn(['min_date_of_birth', 'max_date_of_birth']);
        });
    }
}
