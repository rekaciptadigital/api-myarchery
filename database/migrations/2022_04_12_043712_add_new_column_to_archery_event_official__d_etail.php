<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnToArcheryEventOfficialDEtail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_official_detail', function (Blueprint $table) {
            $table->integer('individual_quota')->default(0);
            $table->integer('club_quota')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_official_detail', function (Blueprint $table) {
            //
        });
    }
}
