<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantSetNullableToReasonRefundAndUploadRefund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn(['reason_refund', 'upload_image_refund']);
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->string("reason_refund")->nullable();
            $table->string("upload_image_refund")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn(['reason_refund', 'upload_image_refund']);
        });
    }
}
