<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldIsHideToVenueMasterPlaceFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('venue_master_place_facilities', function (Blueprint $table) {
            $table->boolean('is_hide')->default(false)->after('eo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('venue_master_place_facilities', function (Blueprint $table) {
            $table->dropColumn("is_hide");
        });
    }
}
