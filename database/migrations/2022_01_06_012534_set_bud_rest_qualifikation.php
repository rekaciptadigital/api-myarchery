<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetBudRestQualifikation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bud_rest_qualifikations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_category_detail_id')->unsigned();
            $table->integer('bud_rest_start');
            $table->integer('bud_rest_end');
            $table->integer('target_face')->default(4);
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
        //
    }
}
