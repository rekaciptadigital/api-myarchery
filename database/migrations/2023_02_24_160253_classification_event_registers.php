<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ClassificationEventRegisters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('classification_event_registers')) {
            Schema::create('classification_event_registers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('event_id');
                $table->bigInteger('children_classification')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('country_id')->nullable();
                $table->bigInteger('provinsi_id')->nullable();
                $table->bigInteger('city_id')->nullable();
                $table->bigInteger('archery_club_id')->nullable();
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classification_event_registers');
    }
}
