<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableVenueProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('venue_place_product_customs')) {
            Schema::rename('venue_place_product_customs', 'venue_place_products');
        }

        if (Schema::hasTable('venue_place_products')) {
            Schema::table('venue_place_products', function (Blueprint $table) {
                $table->string("description")->nullable()->after("product_name");
                $table->dropColumn('total_quota_per_day');
                $table->boolean("has_session")->default(false)->after("weekend_price");
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venue_place_product_customs');
    }
}
