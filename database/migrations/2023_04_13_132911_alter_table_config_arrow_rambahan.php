<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableConfigArrowRambahan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_arrow_rambahan', function (Blueprint $table) {
            $table->dropColumn("session");
            $table->dropColumn("arrow");
            $table->dropColumn("rambahan");
        });

        Schema::table('category_config', function (Blueprint $table) {
            $table->integer("have_special_category")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_arrow_rambahan', function (Blueprint $table) {
            $table->integer("session")->default(2);
            $table->integer("arrow")->default(6);
            $table->integer("rambahan")->default(6);
        });

        Schema::table('category_config', function (Blueprint $table) {
            $table->dropColumn("have_special_category");
        });
    }
}
