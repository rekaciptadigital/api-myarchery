<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventQualificationScheduleFullDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_qualification_schedule_full_day', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('qalification_time_id')->index('qualification_time_index');
            $table->unsignedInteger('participant_member_id')->index('participant_member_index');
            $table->smallInteger('is-scoring')->default(0);
            $table->smallInteger('bud_rest_number')->default(0);
            $table->string('target_face', 2)->default("");
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
        Schema::dropIfExists('archery_event_qualification_schedule_full_day');
    }
}
