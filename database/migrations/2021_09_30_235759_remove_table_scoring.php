<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTableScoring extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('archery_event_scoring_system_details');
        Schema::dropIfExists('archery_event_scoring_system_categories');
        Schema::dropIfExists('archery_event_scores');
        Schema::dropIfExists('archery_event_end_shoot_scores');
        Schema::dropIfExists('archery_event_end_scores');
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
