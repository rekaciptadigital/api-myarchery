<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoring extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_scorings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('participant_member_id')->index();
            $table->integer('total');
            $table->integer('scoring_session')->index();
            $table->text('scoring_detail');
            $table->integer('type')->index();
            $table->text('scoring_log')->nullable();
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
        Schema::dropIfExists('archery_scorings');
    }
}
