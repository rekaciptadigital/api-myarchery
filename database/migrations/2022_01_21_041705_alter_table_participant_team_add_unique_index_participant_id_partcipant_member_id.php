<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantTeamAddUniqueIndexParticipantIdPartcipantMemberId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('participant_member_teams', 'participant_id')) {
            Schema::table('participant_member_teams', function (Blueprint $table) {
                $table->dropColumn('participant_id');
            });
        }

        if (Schema::hasColumn('participant_member_teams', 'participant_member_id')) {
            Schema::table('participant_member_teams', function (Blueprint $table) {
                $table->dropColumn('participant_member_id');
            });
        }

        Schema::table('participant_member_teams', function (Blueprint $table) {
            $table->integer('participant_member_id')->unsigned()->index();
            $table->integer('participant_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participant_member_teams', function (Blueprint $table) {
            $table->dropColumn('participant_member_id'); 
            $table->dropColumn('participant_id');
        });
    }
}
