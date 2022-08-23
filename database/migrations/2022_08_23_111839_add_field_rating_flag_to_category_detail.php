<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldRatingFlagToCategoryDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->smallInteger("rating_flag")->default(1)->comment("1 untuk dimasukkan ke semua pemeringkatan, 2 untuk pemeringkatan khusus diluar semua pemeringkatan, dan 3 bisa masuk ke semua pemeringkatan atau khusus");
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
            $table->dropColumn("rating_flag");
        });
    }
}
