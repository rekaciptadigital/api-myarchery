<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventCategoryCompetitionDistances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_category_competition_distances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_category_competition_id');
            $table->string('distance_id');
            $table->timestamps();
        });

        Schema::table('archery_event_category_competitions', function (Blueprint $table) {
            $table->dropColumn('distances');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_category_competitions', function (Blueprint $table) {
            $table->string('distances');
        });
        Schema::dropIfExists('archery_event_category_competition_distances');
    }
}
