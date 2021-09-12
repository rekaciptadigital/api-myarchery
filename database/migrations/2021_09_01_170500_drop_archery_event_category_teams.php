<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropArcheryEventCategoryTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('archery_event_category_teams');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('archery_event_category_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_category_id');
            $table->string('team_category_id');
            $table->integer('quota');
            $table->timestamps();
        });
    }
}
