<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_type');
            $table->text('poster')->nullable();
            $table->text('handbook')->nullable();
            $table->string('event_name');
            $table->dateTime('registration_start_datetime');
            $table->dateTime('registration_end_datetime');
            $table->dateTime('event_start_datetime');
            $table->dateTime('event_end_datetime');
            $table->text('location');
            $table->enum('location_type', ['Indoor', 'Outdoor', 'Both']);
            $table->text('description');
            $table->boolean('is_flat_registration_fee');
            $table->date('published_datetime')->nullable();
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
        Schema::dropIfExists('archery_events');
    }
}
