<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVenues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venue_places', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('eo_id')->unsigned()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('type')->default(0)->index()->comment('1: indoor, 2: outdoor, 3: indoor & outdoor');
            $table->string('phone_number');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('address');
            $table->integer('province_id');
            $table->integer('city_id');
            $table->timestamps();
        });
        Schema::create('venue_master_place_facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->integer('eo_id')->default(0);
            $table->timestamps();
        });
        Schema::create('venue_place_facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('place_id')->unsigned()->index();
            $table->integer('master_place_facility_id');
            $table->timestamps();
        });
        Schema::create('venue_place_galleries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('place_id')->unsigned()->index();
            $table->string('file');
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
        Schema::dropIfExists('venue_places');
        Schema::dropIfExists('venue_master_place_facilities');
        Schema::dropIfExists('venue_place_facilities');
        Schema::dropIfExists('venue_place_galleries');
    }
}
