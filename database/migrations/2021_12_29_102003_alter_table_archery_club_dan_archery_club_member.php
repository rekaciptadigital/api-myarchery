<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArcheryClubDanArcheryClubMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('club_members', 'archery_club_members');

        Schema::table('archery_clubs', function (Blueprint $table) {
            $table->dropColumn('province');
            $table->dropColumn('city');
        });

        Schema::table('archery_clubs', function (Blueprint $table) {
            $table->integer('province')->index();
            $table->integer('city')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('archery_club_members', 'club_members');


        Schema::table('archery_clubs', function (Blueprint $table) {
            $table->dropColumn('province');
            $table->dropColumn('city');
        });
    }
}
