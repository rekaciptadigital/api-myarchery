<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldWnaCaseToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("passport_number")->nullable();
            $table->smallInteger("is_wna")->default(0);
            $table->integer("country_id")->default(0);
            $table->integer("city_of_country_id")->default(0);
            $table->string("passport_img")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(["passport_number", "is_wna", "country_id", "city_of_country_id", "passport_img"]);
        });
    }
}
