<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCategoryDetailAddFieldEarlyBirdAndDateEarlyBird extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_category_details', function (Blueprint $table) {
            $table->decimal('early_bird', 19, 4)->default(0);
            $table->dateTime('end_date_early_bird')->nullable();
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
            $table->dropColumn(['early_bird', 'end_date_early_bird']);
        });
    }
}
