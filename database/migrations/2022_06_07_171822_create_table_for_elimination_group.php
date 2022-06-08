<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableForEliminationGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_scoring_elimination_group', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("participant_id");
            $table->integer("elimination_match_group_id");
            $table->text("scoring_detail")->nullable();
            $table->text("scoring_log")->nullable();
            $table->integer("admin_total")->default(0);
            $table->unique(['participant_id', 'elimination_match_group_id'], "scoring_elimination_unique");
            $table->timestamps();
        });

        Schema::create('archery_event_elimination_group', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("category_id")->unique();
            $table->integer("count_participant");
            $table->integer("elimination_scoring_type")->index();
            $table->timestamps();
        });

        Schema::create('archery_event_elimination_group_member_team', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("participant_id");
            $table->integer("member_id");
            $table->unique(['participant_id', 'member_id'], "participant_member_unique");
            $table->timestamps();
        });

        Schema::create('archery_event_elimination_group_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("participant_id")->unique();
            $table->integer("thread")->index();
            $table->timestamps();
        });

        Schema::create('archery_event_elimination_group_match', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("elimination_group_id");
            $table->integer("group_team_id");
            $table->integer("round");
            $table->integer("match");
            $table->integer("win");
            $table->integer("index")->index();
            $table->integer("result");
            $table->integer("bud_rest");
            $table->integer("target_face");
            $table->unique(['elimination_group_id', 'group_team_id', 'round', 'match'], "participant_member_elimination_unique");
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
        Schema::dropIfExists('table_for_elimination_group');
    }
}
