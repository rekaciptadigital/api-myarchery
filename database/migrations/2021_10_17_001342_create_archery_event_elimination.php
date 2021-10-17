<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventElimination extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_elimination_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->index();
            $table->integer('thread')->index();
            $table->integer('position_qualification')->nullable();
            $table->text('log')->nullable();
            $table->timestamps();
        });

        Schema::create('archery_event_elimination_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_category_id')->index();
            $table->date('date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('archery_event_elimination_matches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('elimination_member_id');
            $table->integer('elimination_schedule_id');
            $table->integer('round');
            $table->integer('match');
            $table->index(['elimination_schedule_id',"elimination_member_id"],"schedule_member");
            $table->index(['match',"round"],"match_round");
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
        Schema::dropIfExists('archery_event_elimination');
        Schema::dropIfExists('archery_event_elimination_schedules');
        Schema::dropIfExists('archery_event_elimination_matches');
    }
}
