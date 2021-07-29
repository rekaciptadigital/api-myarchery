<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('menu_item_permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('menu_item_id')->index();
                $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
                $table->unsignedInteger('permission_id')->unsigned();
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('restrict');
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
        Schema::dropIfExists('menu_item_permissions');
    }
}
