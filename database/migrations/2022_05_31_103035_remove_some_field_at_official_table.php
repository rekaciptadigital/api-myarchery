<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSomeFieldAtOfficialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_official', function (Blueprint $table) {
            $table->dropColumn(["relation_with_participant", "relation_with_participant_label", "team_category_id", "age_category_id", "competition_category_id", "distance_id"]);
        });

        Schema::table('archery_event_official_detail', function (Blueprint $table) {
            $table->dropColumn(["individual_quota", "club_quota"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
