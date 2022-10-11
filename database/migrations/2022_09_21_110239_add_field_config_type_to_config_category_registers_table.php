<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldConfigTypeToConfigCategoryRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_category_registers', function (Blueprint $table) {
            $table->dropColumn("team_category_id");
            $table->smallInteger("config_type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_category_registers', function (Blueprint $table) {
            //
        });
    }
}
