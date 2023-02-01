<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id")->index();
            $table->integer("transaction_log_id")->index();
            $table->integer("status")->comment("status transaksi")->index();
            $table->integer("total_price");
            $table->integer("is_early_bird_payment")->comment("menandakan jika dia bayar pakai harga early bird atau tidak");
            $table->timestamps();
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->integer("order_event_id")->default(0)->index();
        });

        Schema::table('archery_event_participant_members', function (Blueprint $table) {
            $table->dropColumn("archery_event_category_detail_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_events');
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn("order_event_id");
        });
    }
}
