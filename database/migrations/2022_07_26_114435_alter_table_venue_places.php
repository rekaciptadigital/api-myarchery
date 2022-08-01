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
            $table->tinyInteger('status')->default(1)->comment("1: draft, 2: diajukan, 3: aktif, 4: non-aktif, 5: ditolak")->after('city_id');
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
