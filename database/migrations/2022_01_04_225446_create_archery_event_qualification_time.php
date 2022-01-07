<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventQualificationTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_qualification_time', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_detail_id');
            $table->dateTime('event_start_datetime');
            $table->dateTime('event_end_datetime');
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
        Schema::dropIfExists('archery_event_qualification_time');
    }
}
