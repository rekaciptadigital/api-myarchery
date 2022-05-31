<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldBudrsetNumberToTableEliminationMatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_elimination_matches', function (Blueprint $table) {
            $table->integer("bud_rest")->default(0);
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
        Schema::table('archery_event_elimination_matches', function (Blueprint $table) {
            $table->dropColumn(["bud_rest", "target_face"]);
        });
    }
}
