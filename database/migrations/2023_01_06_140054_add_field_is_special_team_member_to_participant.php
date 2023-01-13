<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldIsSpecialTeamMemberToParticipant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->integer("is_special_team_member")->default(0);
        });

        Schema::create("team_member_special", function (Blueprint $table) {
            $table->increments('id');
            $table->integer("participant_individual_id");
            $table->integer("participant_team_id");
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
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn("is_special_team_member");
        });

        Schema::dropIfExists("team_member_special");
    }
}
