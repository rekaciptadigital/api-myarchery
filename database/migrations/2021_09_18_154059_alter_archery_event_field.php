<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArcheryEventField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->string('pic_call_center');
            $table->dateTime('event_start_elimination')->nullable();
            $table->dateTime('event_end_elimination')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->dropColumn('pic_call_center');
            $table->dropColumn('event_start_elimination');
            $table->dropColumn('event_end_elimination');
        });
    }
}
