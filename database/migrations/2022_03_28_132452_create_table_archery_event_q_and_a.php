<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableArcheryEventQAndA extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_archery_event_q_and_a', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("event_id")->index();
            $table->integer("sort")->default(0);
            $table->string("title")->nullable();
            $table->text("description")->nullable();
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
        Schema::dropIfExists('table_archery_event_q_and_a');
    }
}
