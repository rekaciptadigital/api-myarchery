<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminLoginTokenUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_login_tokens', function (Blueprint $table) {
            $table->integer("admin_id")->index();
        });

        Schema::table('user_login_tokens', function (Blueprint $table) {
            $table->integer("user_id")->index();
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
