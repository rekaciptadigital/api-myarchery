<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserVerifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('user_verifications', function (Blueprint $table) {
                $table->increments('id');
                $table->bigInteger('user_id')->nullable();
                $table->string('verification_token')->nullable()->unique();
                $table->dateTime('expired_at')->nullable();
                $table->integer('count_incorrect')->nullable();
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
        Schema::dropIfExists('user_verifications');
    }
}
