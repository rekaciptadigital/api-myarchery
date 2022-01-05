<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::dropIfExists('archery_clubs');

            Schema::create('archery_clubs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('logo')->nullable();
                $table->string('banner')->nullable();
                $table->string('place_name');
                $table->string('province');
                $table->string('city');
                $table->text('address');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } catch (PDOException $ex) {
            $this->down();
            throw $ex;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archery_clubs');
    }
}
