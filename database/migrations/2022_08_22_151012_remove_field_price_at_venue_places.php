<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFieldPriceAtVenuePlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('venue_places', 'weekday_price') && Schema::hasColumn('venue_places', 'weekend_price')) {
            Schema::table('venue_places', function (Blueprint $table) {
                $table->dropColumn('weekday_price');
                $table->dropColumn('weekend_price');
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
        //
    }
}
