<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldArrayRankCanChangeToArcheryEventParticipantMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participant_members', function (Blueprint $table) {
            $table->text("rank_can_change")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_participant_members', function (Blueprint $table) {
            $table->dropColumn("rank_can_change");
        });
    }
}
