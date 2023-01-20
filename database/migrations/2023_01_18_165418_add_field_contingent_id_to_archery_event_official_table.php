<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldContingentIdToArcheryEventOfficialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_official', function (Blueprint $table) {
            $table->integer("city_id")->default(0)->comment("digunakan untuk menunjuk ke contingent id pada event, jika event tersebut menggunakan contingent");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_official', function (Blueprint $table) {
            $table->dropColumn("city_id");
        });
    }
}
