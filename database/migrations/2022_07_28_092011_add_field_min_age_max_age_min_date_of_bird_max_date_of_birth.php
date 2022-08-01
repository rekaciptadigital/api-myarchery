<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldMinAgeMaxAgeMinDateOfBirdMaxDateOfBirth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->dateTime("min_date_of_birth")->nullable()->comment("tanggal lahir minimal user yang bisa daftar event");
            $table->dateTime("max_date_of_birth")->nullable()->comment("tanggal lahir maksimal user yang bisa daftar event");
            $table->integer("min_age")->default(0);
            $table->integer("max_age")->default(0);
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
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->dropColumn(['min_date_of_birth', 'max_date_of_birth', 'min_age', 'max_age', 'is_age']);
        });
    }
}
