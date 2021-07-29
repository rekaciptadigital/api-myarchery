<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key')->unique();
                $table->string('label')->nullable();
                $table->string('description')->nullable();
                $table->boolean('always_allow')->default(false);
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
        Schema::dropIfExists('permissions');
    }
}
