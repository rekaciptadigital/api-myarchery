<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArcheryEventParticipantAddUniqueField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropIndex('archery_event_participants_user_id_index');
            $table->dropIndex('archery_event_participants_event_category_id_index');
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->unique(['user_id', 'event_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
  
    }
}
