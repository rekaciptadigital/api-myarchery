<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('archery_age_category_id')->index();
            $table->date('max_date_of_birth');
            $table->unsignedInteger('archery_category_id')->index();
            $table->float('distance');
            $table->integer('quota');
            $table->boolean('allow_individual');
            $table->decimal('individual_registration_price')->nullable();
            $table->boolean('allow_group');
            $table->decimal('group_registration_price')->nullable();
            $table->timestamps();
            $table->foreign('archery_age_category_id')->references('id')->on('archery_age_categories')->onDelete('restrict');
            $table->foreign('archery_category_id')->references('id')->on('archery_categories')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archery_event_categories');
    }
}
