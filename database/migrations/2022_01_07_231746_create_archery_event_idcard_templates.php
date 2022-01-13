<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventIdcardTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_idcard_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->index();
            $table->longText('html_template');
            $table->longText('editor_data');
            $table->text('background_url')->nullable();
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
        Schema::dropIfExists('archery_event_idcard_templates');
    }
}
