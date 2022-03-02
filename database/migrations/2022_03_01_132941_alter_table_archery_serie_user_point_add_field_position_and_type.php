<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArcherySerieUserPointAddFieldPositionAndType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_serie_user_point', function (Blueprint $table) {
            $table->integer('position');
            $table->enum("type", ["elimination", "disqualification"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_serie_user_point', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->dropColumn('type');
        });
    }
}
