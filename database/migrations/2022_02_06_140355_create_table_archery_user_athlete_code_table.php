<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableArcheryUserAthleteCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_archery_user_athlete_code', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->integer('sequence')->unsigned();
            $table->string('prefix', 15);
            $table->integer('user_id')->index();
            $table->timestamps();
            $table->primary(array('prefix', 'sequence'));
        });
        DB::statement('ALTER TABLE table_archery_user_athlete_code MODIFY sequence INTEGER NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_archery_user_athlete_code');
    }
}
