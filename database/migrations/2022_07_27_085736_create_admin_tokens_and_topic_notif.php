<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminTokensAndTopicNotif extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_login_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->text('firebase_token')->nullable();
            $table->string('private_signature',255);
            $table->dateTime('expired_at');
            $table->timestamps();
        });
        Schema::create('admin_notif_topic', function (Blueprint $table) {
            $table->integer('admin_id');
            $table->string('topic',255);
            $table->timestamps();
            $table->primary(array('admin_id', 'topic'));
        });

        Schema::create('user_login_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->text('firebase_token')->nullable();
            $table->string('private_signature',255);
            $table->dateTime('expired_at');
            $table->timestamps();
        });
        Schema::create('user_notif_topic', function (Blueprint $table) {
            $table->integer('user_id');
            $table->string('topic',255);
            $table->timestamps();
            $table->primary(array('user_id', 'topic'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_login_tokens');
        Schema::dropIfExists('admin_notif_topic');
        Schema::dropIfExists('user_login_tokens');
        Schema::dropIfExists('user_notif_topic');
    }
}
