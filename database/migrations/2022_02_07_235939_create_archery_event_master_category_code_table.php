<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventMasterCategoryCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_master_category_code', function (Blueprint $table) {
            $table->increments('id');
            $table->string('age_category_id');
            $table->string('distance_category_id');
            $table->string('competition_category_id');
            $table->string('team_category_id');
            $table->unique(['age_category_id', 'distance_category_id', 'competition_category_id', 'team_category_id'], "unique_age_ditance_competition_team");
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
        Schema::dropIfExists('archery_event_master_category_code');
    }
}
