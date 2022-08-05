<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVenueCapacityArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    public function up()
    {
        Schema::table('venue_places', function (Blueprint $table) {
            $table->tinyInteger('budrest_quantity')->default(0)->after('status');
            $table->tinyInteger('target_quantity')->default(0)->after('budrest_quantity');
            $table->tinyInteger('arrow_quantity')->default(0)->after('target_quantity');
            $table->tinyInteger('people_quantity')->default(0)->after('arrow_quantity');
        });
        Schema::create('venue_master_place_capacity_area', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('distance');
            $table->integer('eo_id')->default(0);
            $table->timestamps();
        });
        Schema::create('venue_place_capacity_area', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('place_id')->unsigned()->index();
            $table->integer('master_place_capacity_area_id');
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
        Schema::dropIfExists('table_venue_capacity_area');
    }
}
