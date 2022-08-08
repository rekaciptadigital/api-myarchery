<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->integer("include_payment_gateway_fee_to_user")->default(0);
            $table->integer("include_my_archery_fee_to_user")->default(0);
            $table->float("my_archery_fee_percentage")->default(0);
        });

        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->double("include_payment_gateway_fee")->default(0);
            $table->double("include_my_archery_fee")->default(0);
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
