<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('admin_id')->index();
                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
                $table->unsignedInteger('role_id')->index();
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->timestamps();
            });
        } catch (PDOException $ex) {
            $this->down();
            throw $ex;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
}
