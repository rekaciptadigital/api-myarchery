<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventOfficialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_official', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('type')->default(1);
            $table->integer('club_id')->default(0)->unsigned()->index();
            $table->integer('relation_with_participant')->default(0);
            $table->string('relation_with_participant_label')->nullable();
            $table->integer('status')->default(4)->comment('4 untuk pending, 3 untuk diproses, 2 gagal, 1 success');
            $table->integer('transaction_log_id')->unsigned()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archery_event_official');
    }
}
