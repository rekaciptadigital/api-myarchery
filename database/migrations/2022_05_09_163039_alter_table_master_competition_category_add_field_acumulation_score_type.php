<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMasterCompetitionCategoryAddFieldAcumulationScoreType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_competition_categories', function (Blueprint $table) {
            $table->smallInteger('scooring_accumulation_type')->default(0)->comment("1 untuk akumulasi point 2 untuk akumulasi skor");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_master_competition_categories', function (Blueprint $table) {
            $table->dropColumn('scooring_accumulation_type');
        });
    }
}
