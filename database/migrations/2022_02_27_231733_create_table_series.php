<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSeries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_series', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->integer('eo_owner')->unsigned()->index();
            $table->timestamps();
        });
        Schema::create('archery_event_series', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('serie_id')->unsigned()->index();
            $table->integer('event_id')->unsigned()->index();
            $table->integer('sort')->unsigned()->index();
            $table->timestamps();
        });
        Schema::create('archery_serie_master_point', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('serie_id')->unsigned()->index();
            $table->integer('start_pos')->unsigned()->index();
            $table->integer('end_pos')->unsigned()->index();
            $table->integer('type')->default(0)->index()->comment('1 qualification, 2 elimination');
            $table->integer('point')->default(0);
            $table->timestamps();
        });
        Schema::create('archery_serie_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('serie_id')->unsigned()->index();
            $table->string("age_category_id",225);
            $table->string("competition_category_id",225);
            $table->string("distance_id",225);
            $table->string("team_category_id",225);
            $table->index(["age_category_id","competition_category_id","distance_id","team_category_id"],"event_category");
            $table->timestamps();
        });
        Schema::create('archery_serie_user_point', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_serie_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('event_category_id')->unsigned()->index();
            $table->integer('point')->default(0);
            $table->integer('status')->default(0)->index()->comment('1 dihitung, 0 tidak dihitung');
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
        Schema::dropIfExists('table_series');
    }
}
