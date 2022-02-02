<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventParticipantMemberNumbers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('archery_event_participant_member_numbers')) {
            Schema::create('archery_event_participant_member_numbers', function (Blueprint $table) {
                $table->engine = 'MyISAM';
                $table->integer('sequence')->unsigned();
                $table->string('prefix', 15);
                $table->integer('user_id')->index();
                $table->integer('event_id')->index();
                $table->timestamps();
                $table->primary(array('prefix', 'sequence'));
            });
        }
        DB::statement('ALTER TABLE archery_event_participant_member_numbers MODIFY sequence INTEGER NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archery_event_participant_member_numbers');
    }
}
