<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusFieldAtVenuePlacesTable extends Migration
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
            $table->smallInteger('status')->default(1)->comment("1: draft, 2: diajukan, 3: lengkapi-data, 4: aktif, 5: non-aktif, 6: ditolak")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('venue_places', function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
}
