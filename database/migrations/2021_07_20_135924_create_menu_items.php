<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('menu_id')->nullable();
                $table->string('label');
                $table->string('url');
                $table->string('target')->default('_self');
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->integer('parent_id')->nullable();
                $table->integer('order');
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
        Schema::dropIfExists('menu_items');
    }
}
