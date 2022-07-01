<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldEliminationRankToEliminationGroupTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_elimination_group_teams', function (Blueprint $table) {
            $table->integer("elimination_ranked")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_elimination_group_teams', function (Blueprint $table) {
            $table->dropColumn("elimination_ranked");
        });
    }
}
