<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVenueProducts extends Migration
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
            $table->decimal('weekday_price', 19, 4)->default(0)->after('people_quantity');
            $table->decimal('weekend_price', 19, 4)->default(0)->after('weekday_price');
        });
        Schema::create('venue_place_product_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_operational_id')->unsigned()->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('total_budrest');
            $table->integer('total_target');
            $table->integer('max_capacity');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('venue_place_product_customs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('place_id')->unsigned()->index();
            $table->string('product_name');
            $table->enum('base_product', ['Arrow', 'Target', 'Bantalan', 'Orang', 'Hari', 'Jam']);
            $table->integer('total_quota_per_day')->default(0);
            $table->integer('total_each_rent_per_day')->default(0);
            $table->decimal('weekday_price', 19, 4)->default(0);
            $table->decimal('weekend_price', 19, 4)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venue_place_product_sessions');
        Schema::dropIfExists('venue_place_product_customs');
    }
}
