<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWnaPriceToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->decimal('normal_price_wna', 19, 4)->default(0);
            $table->decimal('early_price_wna', 19, 4)->default(0);
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
