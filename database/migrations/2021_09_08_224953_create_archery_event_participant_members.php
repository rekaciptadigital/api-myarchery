<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventParticipantMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_participant_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('archery_event_participant_id');
            $table->string('name');
            $table->string('team_category_id');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('club')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('qualification_date')->nullable();
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
        Schema::dropIfExists('archery_event_participant_members');
    }
}
