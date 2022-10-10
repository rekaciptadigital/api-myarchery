<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigArrowRambahanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_arrow_rambahan', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("event_id");
            $table->smallInteger("type")->comment("1 all category, 2 special category");
            $table->timestamps();
        });

        Schema::create('category_config_mapping_arrow_rambahan', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("config_arrow_rambahan_id");
            $table->integer("category_id");
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
        Schema::dropIfExists('config_arrow_rambahan');
        Schema::dropIfExists('category_config_mapping_arrow_rambahan');
    }
}
