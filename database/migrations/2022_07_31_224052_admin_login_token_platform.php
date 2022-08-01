<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminLoginTokenPlatform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_login_tokens', function (Blueprint $table) {
            $table->string("platform",25)->default("unknown");
        });
        Schema::table('user_login_tokens', function (Blueprint $table) {
            $table->string("platform",25)->default("unknown");
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
