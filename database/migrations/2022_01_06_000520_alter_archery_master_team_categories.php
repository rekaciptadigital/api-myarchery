<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArcheryMasterTeamCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_team_categories', function (Blueprint $table) {
            $table->enum('type', ['Individual', 'Team'])->index();
            $table->tinyInteger('is_hide')->default(0)->comment("0 untuk hide dan 1 untuk show");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_master_team_categories', function (Blueprint $table) {
            //
        });
    }
}
