<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("config_arrow_rambahan_id");
            $table->integer("session");
            $table->integer("arrow");
            $table->integer("rambahan");
            $table->timestamps();
        });

        Schema::table('config_arrow_rambahan', function (Blueprint $table) {
            $table->integer("session");
            $table->integer("arrow");
            $table->integer("rambahan");
        });

        Schema::table('category_config_mapping_arrow_rambahan', function (Blueprint $table) {
            $table->dropColumn("config_arrow_rambahan_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_config');
    }
}
