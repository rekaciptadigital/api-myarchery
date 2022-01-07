<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArcheryEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('archery_events', function (Blueprint $table) {
            $table->dropColumn('event_type');
            $table->enum('event_competition', ['Tournament', 'Games'])->index();
            $table->unsignedInteger('city_id');
            $table->boolean('is_flat_registration_fee')->nullable()->change();
            $table->string('pic_call_center')->nullable()->change();
            $table->tinyInteger('status')->default(0)->comment("0 untuk draft dan 1 untuk published");
        });

        Schema::table('archery_events', function (Blueprint $table) {
            $table->enum('event_type', ['Full_day', 'Marathon'])->index();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            //
        });
    }
}
