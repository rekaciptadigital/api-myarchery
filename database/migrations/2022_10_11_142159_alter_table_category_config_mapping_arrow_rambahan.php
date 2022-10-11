<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCategoryConfigMappingArrowRambahan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_config_mapping_arrow_rambahan', function (Blueprint $table) {
            $table->dropColumn("competition_category_id");
            $table->dropColumn("age_category_id");
        });
        Schema::table('category_config_mapping_arrow_rambahan', function (Blueprint $table) {
            $table->string("competition_category_id");
            $table->string("age_category_id");
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
