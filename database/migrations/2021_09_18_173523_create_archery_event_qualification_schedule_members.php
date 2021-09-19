<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventQualificationScheduleMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_qualification_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->integer('qualification_detail_id')->index()->comment('event_qualification_detail_id');
            $table->integer('participant_member_id')->index()->comment('archery_event_participant_member_id');
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
        Schema::dropIfExists('archery_event_qualification_schedule_members');
    }
}
