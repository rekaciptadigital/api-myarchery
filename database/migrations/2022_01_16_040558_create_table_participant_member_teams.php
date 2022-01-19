<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableParticipantMemberTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participant_member_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('archery_club_member_id')->unsigned()->index();
            $table->integer('participant_member_id')->unsigned()->index();
            $table->enum('type', ['individu', 'team']);
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
        Schema::dropIfExists('table_participant_member_teams');
    }
}
