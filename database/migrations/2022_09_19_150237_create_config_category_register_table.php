<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigCategoryRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_category_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("event_id");
            $table->enum("type", [1, 2])->comment("1 individu, 2 beregu");
            $table->dateTime("datetime_start_register")->nullable();
            $table->dateTime("datetime_end_register")->nullable();
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
        Schema::dropIfExists('config_category_registers');
    }
}
