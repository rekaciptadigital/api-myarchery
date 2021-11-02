<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventElimination extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_elimination_matches', function(Blueprint $table)
        {
            $table->renameColumn('event_category_id', 'event_elimination_id');
        });

        Schema::create('archery_event_eliminations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_category_id')->index();
            $table->integer('count_participant');
            $table->integer('elimination_type');
            $table->integer('elimination_scoring_type');
            $table->string('gender',10);
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
        Schema::dropIfExists('archery_event_eliminations');
    }
}
