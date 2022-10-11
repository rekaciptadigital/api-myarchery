<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreColumnToCategoryConfigMappingArrowRambahan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_config_mapping_arrow_rambahan', function (Blueprint $table) {
            $table->dropColumn("category_id");
            $table->integer("competition_category_id");
            $table->integer("age_category_id");
            $table->integer("distance_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_config_mapping_arrow_rambahan', function (Blueprint $table) {
            //
        });
    }
}
