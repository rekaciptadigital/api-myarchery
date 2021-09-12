<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArcheryEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->dateTime('quatification_start_datetime')->nullable();
            $table->dateTime('quatification_end_datetime')->nullable();
            $table->boolean('qualification_weekdays_only')->nullable()->default(false);
            $table->json('qualification_session_length')->nullable();
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
            $table->dropColumn('quatification_start_datetime');
            $table->dropColumn('quatification_end_datetime');
            $table->dropColumn('qualification_weekdays_only');
            $table->dropColumn('qualification_session_length');
        });
    }
}
