<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldCanUpdateProfileToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->smallInteger("can_update_name", false, true)->default(3);
            $table->smallInteger("can_update_date_of_birth", false, true)->default(3);
            $table->smallInteger("can_update_gender", false, true)->default(3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(["can_update_name", "can_update_date_of_birth", "can_update_gender"]);
        });
    }
}
