<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldRulesRatingClubToCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->integer("rules_rating_club")->default(1)->comment("1 untuk semua kategori, 2 untuk satuan khusus, 3 kategori gabungan");
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
            $table->dropColumn("rules_rating_club");
        });
    }
}
