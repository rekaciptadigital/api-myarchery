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
            $table->text('poster');
            $table->text('technical_handbook')->nullable();
            $table->string('name');
            $table->date('registration_start_date');
            $table->date('registration_end_date');
            $table->date('execution_start_date');
            $table->date('execution_end_date');
            $table->string('phone_number');
            $table->text('location');
            $table->enum('location_type', ['Indoor', 'Outdoor', 'Both']);
            $table->decimal('total_price');
            $table->string('total_price_currency')->default('Rp');
            $table->text('description');
            $table->boolean('is_public');
            $table->date('published_datetime');
            $table->unsignedInteger('admin_id')->index();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
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
