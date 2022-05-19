<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArcheryEventParticipantsAddFieldIsPresent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->smallInteger("is_present")->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn("is_present");
        });
    }
}
