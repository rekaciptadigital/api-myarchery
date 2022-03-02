<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserPoinAddFieldMemberId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_serie_user_point', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('archery_serie_user_point', function (Blueprint $table) {
            $table->enum("type", ["qualification", "elimination"]);
            $table->integer("member_id")->unsigned();

            $table->unique(['member_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_serie_user_point', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('member_id');
        });
    }
}
