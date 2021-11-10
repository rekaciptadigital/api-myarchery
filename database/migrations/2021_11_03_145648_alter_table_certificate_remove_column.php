<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCertificateRemoveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_event_certificate_templates', function (Blueprint $table) {
          $table->dropColumn('editor_data');
          $table->dropColumn('background_url');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('archery_event_certificate_templates', function (Blueprint $table) {
        $table->text('editor_data');
        $table->text('background_url');
      });

    }
}
