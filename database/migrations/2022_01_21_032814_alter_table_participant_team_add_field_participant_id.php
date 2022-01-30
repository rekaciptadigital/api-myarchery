<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantTeamAddFieldParticipantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('participant_member_teams', 'type')) {
            Schema::table('participant_member_teams', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasColumn('participant_member_teams', 'archery_club_member_id')) {
            Schema::table('participant_member_teams', function (Blueprint $table) {
                $table->dropColumn('archery_club_member_id');
            });
        }

        Schema::table('participant_member_teams', function (Blueprint $table) {
            $table->enum('type', ['individual', 'team']);
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
            $table->dropColumn('type'); 
            $table->dropColumn('participant_id');
        });
    }
}
