<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldInArcheryEventsClassidication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->bigInteger("parent_classification")->default(0);
            $table->bigInteger("classification_country_id")->default(0);
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->bigInteger("children_classification_id")->default(0);
            $table->bigInteger("classification_country_id")->default(0);
            $table->bigInteger("classification_province_id")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
}
