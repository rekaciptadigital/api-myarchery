<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFiedConfigRegisterIdAndIsSpecialCategoryConfigToArcheryEventCategoryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->integer("config_register_id")->default(0);
            $table->integer("is_special_cat_config")->default(0);
        });

        Schema::table('config_category_registers', function (Blueprint $table) {
            $table->integer("is_have_special")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            //
        });
    }
}
