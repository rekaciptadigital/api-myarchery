<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWithContingentToArcheryEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->smallInteger("with_contingent")->default(0)->comment("bernilai 1 jika event tsb menggunakan format contingent");
            $table->integer("province_id")->default(0)->comment("bernilai 0 jika event tersebut tidak ber format kontingen");
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->integer("city_id")->default(0)->comment("bernilai 0 jika participant tersebut tidak daftar di event yang berformat kontingen");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archery_events', function (Blueprint $table) {
            $table->dropColumn("with_contingent");
            $table->dropColumn("province_id");
        });

        Schema::table('archery_event_participants', function (Blueprint $table) {
            $table->dropColumn("city_id");
        });
    }
}
