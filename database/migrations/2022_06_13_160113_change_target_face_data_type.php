<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTargetFaceDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_elimination_group_match', function (Blueprint $table) {
            $table->dropColumn("target_face");
        });

        Schema::table('archery_event_elimination_group_match', function (Blueprint $table) {
            $table->string("target_face")->default("");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_elimination_group_match', function (Blueprint $table) {
            $table->dropColumn("target_face");
        });
    }
}
