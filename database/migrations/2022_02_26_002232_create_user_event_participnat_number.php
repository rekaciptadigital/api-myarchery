<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserEventParticipnatNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('archery_event_participant_numbers')) {
            Schema::create('archery_event_participant_numbers', function (Blueprint $table) {
                $table->engine = 'MyISAM';
                $table->integer('sequence')->unsigned();
                $table->string('prefix', 15);
                $table->integer('participant_id')->index();
                $table->timestamps();
                $table->primary(array('prefix', 'sequence'));
            });
        }
        DB::statement('ALTER TABLE archery_event_participant_numbers MODIFY sequence INTEGER NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_event_participnat_number');
    }
}
