<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEventCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_master_age_categories', function (Blueprint $table) {
            $table->integer('max_age')->default(0);
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->integer('event_category_id')->default(0)->index();
        });

        Schema::create('archery_event_category_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("event_id");
            $table->string("age_category_id",225);
            $table->string("competition_category_id",225);
            $table->string("distance_id",225);
            $table->string("team_category_id",225);
            $table->integer("quota");
            $table->index(['event_id',"age_category_id","competition_category_id","distance_id","team_category_id"],"event_category");
            $table->timestamps();
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
            $table->removeColumn('max_age');
        });
    }
}
