<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMasterAgeAddFieldIsHide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->smallInteger("is_hide")->default(0)->comment("apakah kelas tersebut digunakan atau tidak");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->dropColumn("is_hide");
        });
    }
}
