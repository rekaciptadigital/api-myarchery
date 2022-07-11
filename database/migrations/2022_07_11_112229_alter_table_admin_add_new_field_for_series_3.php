<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAdminAddNewFieldForSeries3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
            $table->dropColumn('place_of_birth');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->integer('province_id');
            $table->integer('city_id');
            $table->text("intro");
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
