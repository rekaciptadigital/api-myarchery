<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantMemberTeamAddFieldEventCategoryId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participant_member_teams', function (Blueprint $table) {
            $table->dropIndex('participantId_memberId_unique');
        });

        Schema::table('participant_member_teams', function (Blueprint $table) {
            $table->integer('event_category_id')->unsigned();
            $table->unique(['participant_id', 'participant_member_id', 'event_category_id'], 'participantId_memberId_categoryId_unique');
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
            $table->dropColumn('event_category_id');
        });
    }
}
