<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEventEliminationSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_elimination_schedules', function(Blueprint $table)
        {
            $table->renameColumn('event_category_id', 'event_id');

        });
        Schema::table('archery_event_elimination_matches', function(Blueprint $table)
        {
            $table->integer('event_category_id')->index();

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
