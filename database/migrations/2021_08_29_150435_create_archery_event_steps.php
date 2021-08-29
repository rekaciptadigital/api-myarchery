<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->dateTime('qualification_start_datetime')->nullable();
            $table->dateTime('qualification_end_datetime')->nullable();
            $table->integer('qualification_session_per_day')->nullable();
            $table->integer('qualification_quota_per_day')->nullable();
            $table->dateTime('elimination_start_datetime')->nullable();
            $table->dateTime('elimination_end_datetime')->nullable();
            $table->integer('elimination_session_per_day')->nullable();
            $table->integer('elimination_quota_per_day')->nullable();
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
        Schema::dropIfExists('archery_event_steps');
    }
}
