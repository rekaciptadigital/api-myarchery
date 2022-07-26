<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableVenuePlaces extends Migration
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
            $table->dropColumn('type');
            $table->enum('place_type', ['Indoor', 'Outdoor', 'Both'])->after('type');
            $table->tinyInteger('status')->default(0)->comment("0: draft, 1: diajukan, 2: aktif, 3: non-aktif, 4: ditolak")->after('city_id');
        });
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
