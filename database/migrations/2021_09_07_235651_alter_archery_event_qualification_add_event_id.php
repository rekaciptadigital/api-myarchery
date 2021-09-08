<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArcheryEventQualificationAddEventId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_qualifications', function (Blueprint $table) {
            $table->unsignedInteger('event_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_qualifications', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });
    }
}
