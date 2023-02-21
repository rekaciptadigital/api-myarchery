<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMemberRankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_rank', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("rank")->default(0);
            $table->integer("member_id")->index();
            $table->integer("category_id")->index();
            $table->timestamps();
        });

        Schema::table('archery_event_participant_members', function (Blueprint $table) {
            $table->integer("have_coint_tost")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_rank');
        Schema::table('archery_event_participant_members', function (Blueprint $table) {
            $table->dropColumn("have_coint_tost");
        });
    }
}
