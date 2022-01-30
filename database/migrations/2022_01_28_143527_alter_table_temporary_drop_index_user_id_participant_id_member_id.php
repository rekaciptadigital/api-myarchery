<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTemporaryDropIndexUserIdParticipantIdMemberId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_temporrary_member', function (Blueprint $table) {
            $table->dropUnique('unique_participant_member_user');
            $table->dropIndex('table_temporrary_member_event_category_id_index');
        });

        Schema::table('table_temporrary_member', function (Blueprint $table) {
            $table->unique(['participant_member_id', 'event_category_id'], 'unique_member_category');
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
