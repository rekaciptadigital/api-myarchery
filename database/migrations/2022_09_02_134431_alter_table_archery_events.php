<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArcheryEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE `archery_events` CHANGE `event_competition` `event_competition` ENUM('Tournament','Games','Selection') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
    
        Schema::table('archery_events', function (Blueprint $table) {
            $table->boolean("is_private")->default(false)->after('status');
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
            $table->dropColumn("is_private");
        });
    }
}
