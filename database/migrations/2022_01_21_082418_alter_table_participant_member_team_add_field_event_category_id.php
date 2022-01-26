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
            $table->integer('event_category_id')->unsigned()->index();
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
