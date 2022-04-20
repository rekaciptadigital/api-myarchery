<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArcheryEventOfficialFieldAddNewField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_official', function (Blueprint $table) {
            $table->string('team_category_id');
            $table->string('age_category_id');
            $table->string('competition_category_id');
            $table->integer('distance_id');
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
            //
        });
    }
}
