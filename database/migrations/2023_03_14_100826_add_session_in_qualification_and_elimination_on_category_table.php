<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSessionInQualificationAndEliminationOnCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->integer("session_in_qualification_selection")->default(2);
            $table->integer("session_in_elimination_selection")->default(5);
            $table->integer("count_shoot_elimination_selection")->default(3);
        });

        Schema::table('archery_events', function (Blueprint $table) {
            $table->integer("type_formula_irate")->default(1)->comment("type perhitungan irate jika event menggunakan irate");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->dropColumn(["session_in_qualification_selection", "session_in_elimination_selection", "count_shoot_elimination_selection"]);
        });

        Schema::table('archery_events', function (Blueprint $table) {
            $table->dropColumn("type_formula_irate");
        });
    }
}
