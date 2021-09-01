<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventCategoryCompetitionTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_category_competition_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_category_competition_id');
            $table->string('team_category_id');
            $table->string('team_category_label');
            $table->integer('quota');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archery_event_category_competition_teams');
    }
}
