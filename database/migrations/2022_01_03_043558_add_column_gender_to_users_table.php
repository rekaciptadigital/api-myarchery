<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnGenderToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'place_of_birth']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
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
            $table->dropColumn(['date_of_birth', 'gender']);
        });
    }
}
