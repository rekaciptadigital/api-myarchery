<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantAddColumnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // update archery_event_participants a join transaction_logs b on a.transaction_log_id = b.id AND b.status = 1 set a.status = 1 

    public function up()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->smallInteger('status')->index()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
