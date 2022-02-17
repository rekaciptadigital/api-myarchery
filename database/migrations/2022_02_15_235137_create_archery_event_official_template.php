<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventOfficialTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_official_template', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('foreground')->nullable();
            $table->longText('background')->nullable();
            $table->longText('logo_event')->nullable();
            $table->longText('html_template');
            $table->integer('event_id');
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
        Schema::dropIfExists('archery_event_official_template');
    }
}
