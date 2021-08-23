<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserArcheryInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('user_archery_info', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedInteger('archery_category_id')->index();
                $table->foreign('archery_category_id')->references('id')->on('archery_categories')->onDelete('restrict');
                $table->unsignedInteger('archery_club_id')->index();
                $table->foreign('archery_club_id')->references('id')->on('archery_clubs')->onDelete('restrict');
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
        Schema::dropIfExists('user_archery_info');
    }
}
