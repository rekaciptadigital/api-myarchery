<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTemporraryMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_temporrary_member', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('participant_id')->unsigned();
            $table->integer('participant_member_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->unique(['participant_id', 'participant_member_id', 'user_id'], 'unique_participant_member_user');
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
        Schema::dropIfExists('table_temporrary_member');
    }
}
