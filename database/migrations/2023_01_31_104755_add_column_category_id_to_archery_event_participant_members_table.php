<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCategoryIdToArcheryEventParticipantMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participant_members', function (Blueprint $table) {
            $table->integer("archery_event_category_detail_id")->default(0);
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
            $table->dropColumn("archery_event_category_detail_id");
        });
    }
}
