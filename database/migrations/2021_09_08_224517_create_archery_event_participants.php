<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_id', 255)->index();
            $table->text('transaction_log_activity');
            $table->integer('amount');
            $table->integer('status');
            $table->timestamps();
        });

        Schema::create('archery_event_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('user_id')->index();
            $table->string('name');
            $table->enum('type',["individual","team"]);
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('club')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('team_category_id');
            $table->string('age_category_id');
            $table->string('competition_category_id');
            $table->integer('distance_id');
            $table->date('qualification_date')->nullable();
            $table->integer('transaction_log_id')->index();
            $table->string('unique_id')->unique()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_logs');
        Schema::dropIfExists('archery_event_participants');
    }
}
