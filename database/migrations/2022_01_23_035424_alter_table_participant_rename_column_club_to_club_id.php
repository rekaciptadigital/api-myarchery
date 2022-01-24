<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantRenameColumnClubToClubId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn('club');
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->integer('club_id')->unsigned()->index();
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
            $table->dropColumn('club_id');
        });
    }
}
