<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserArcheryInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('user_archery_info', function (Blueprint $table) {
            $table->dropForeign('user_archery_info_archery_category_id_foreign');
            $table->dropIndex('user_archery_info_archery_category_id_index');
            $table->unsignedInteger('archery_category_id')->nullable()->change();

            $table->dropForeign('user_archery_info_archery_club_id_foreign');
            $table->dropIndex('user_archery_info_archery_club_id_index');
            $table->unsignedInteger('archery_club_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_archery_info', function (Blueprint $table) {
            $table->unsignedInteger('archery_category_id')->index();
            $table->foreign('archery_category_id')->references('id')->on('archery_categories')->onDelete('restrict');

            $table->unsignedInteger('archery_club_id')->index()->change();
            $table->foreign('archery_club_id')->references('id')->on('archery_clubs')->onDelete('restrict');
        });
    }
}
