<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAcheryEventsAddAdminId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->unsignedInteger('admin_id');
            $table->string('event_slug')->unique();
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
            $table->dropColumn('admin_id');
            $table->dropColumn('event_slug');
        });
    }
}
