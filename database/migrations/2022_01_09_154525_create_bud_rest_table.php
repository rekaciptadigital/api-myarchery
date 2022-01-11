<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudRestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('bud_rest_qualifikations');
        Schema::create('bud_rest', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('archery_event_category_id')->unsigned();
            $table->integer('bud_rest_start');
            $table->integer('bud_rest_end');
            $table->integer('target_face')->default(4);
            $table->enum('type', ['qualification', 'elimination']);
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
        Schema::dropIfExists('bud_rest');
    }
}
