<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventScoringSystemDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_scoring_system_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('archery_event_scoring_system_category_id');
            $table->number('total_session');
            $table->string('round_type_id');
            $table->string('round_type_label');
            $table->number('total_end');
            $table->number('total_shoot');
            $table->string('target_face');
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
        Schema::dropIfExists('archery_event_scoring_system_details');
    }
}
