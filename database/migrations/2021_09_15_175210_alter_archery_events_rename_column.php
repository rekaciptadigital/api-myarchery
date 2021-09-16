<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArcheryEventsRenameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('archery_events', function (Blueprint $table) {
            $table->renameColumn('quatification_start_datetime', 'qualification_start_datetime');
            $table->renameColumn('quatification_end_datetime', 'qualification_end_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('archery_events', function (Blueprint $table) {
            $table->renameColumn('qualification_start_datetime', 'quatification_start_datetime');
            $table->renameColumn('qualification_end_datetime', 'quatification_end_datetime');
        });
    }
}
