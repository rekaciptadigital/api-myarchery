<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderEvent extends Migration
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
            $table->string('order_id',255)->index();
            $table->text('transaction_log_activity');
            $table->integer('amount');
            $table->integer('status');
            $table->timestamps();
        });
        Schema::create('event_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('club_name',255)->nullable();
            $table->string('team_name',255)->nullable();
            $table->string('club_email',255);
            $table->string('club_phone',255);
            $table->integer('event_category')->default(0)->index();
            $table->enum('type',["individual","team"]);
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('archery_event_id')->index();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('archery_event_id')->references('id')->on('archery_events')->onDelete('restrict');
        });
        Schema::create('participants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->string('email',255);
            $table->unsignedInteger('event_order_id')->index();
            $table->enum('gender',["L","P"]);
            $table->timestamps();
            $table->foreign('event_order_id')->references('id')->on('event_orders')->onDelete('restrict');
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
        Schema::dropIfExists('participants');
        Schema::dropIfExists('event_orders');
    }
}
