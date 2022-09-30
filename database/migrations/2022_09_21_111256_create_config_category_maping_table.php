<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigCategoryMapingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_special_maping', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("config_id");
            $table->dateTime("datetime_start_register");
            $table->dateTime("datetime_end_register");
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
        Schema::dropIfExists('config_special_maping');
    }
}
