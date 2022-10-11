<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StartRegisterAndEndRegister extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->dateTime("start_registration")->nullable();
            $table->dateTime("end_registration")->nullable();
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
            $table->dropColumn(["start_registration", "end_registration"]);
        });
    }
}
