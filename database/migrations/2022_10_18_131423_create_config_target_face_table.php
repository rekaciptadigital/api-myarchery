<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigTargetFaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_target_face', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("event_id");
            $table->integer("total_ring")->default(6);
            $table->integer("highest_score")->default(10);
            $table->smallInteger("score_x")->default(1)->comment("1 untuk skor tertinggi dan 2 untuk terendah");
            $table->smallInteger("implement_all")->default(0);
            $table->timestamps();
        });

        Schema::create('config_target_face_per_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("config_id");
            $table->integer("total_ring")->default(6);
            $table->integer("highest_score")->default(10);
            $table->smallInteger("score_x")->default(1)->comment("1 untuk skor tertinggi dan 2 untuk terendah");
            $table->text("categories")->nullable();
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
        Schema::dropIfExists('config_target_face');
    }
}
