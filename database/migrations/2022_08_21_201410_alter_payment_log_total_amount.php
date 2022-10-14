<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentLogTotalAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eo_cash_flow', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('eo_id')->unsigned()->index();
            $table->text('note');
            $table->string('gateway',10)->index();
            $table->integer('transaction_log_id')->unsigned()->index();
            $table->double('amount');
            $table->timestamps();
        });
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->double("total_amount");
            $table->string("gateway",255);
            $table->text("opt");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
