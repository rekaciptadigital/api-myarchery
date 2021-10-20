<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventCertificateTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_certificate_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("event_id")->index();
            $table->text("html_template");
            $table->text("background");
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
        Schema::dropIfExists('archery_event_certificate_templates');
    }
}
