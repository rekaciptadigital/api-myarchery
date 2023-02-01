<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableExcellCollectiveMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excell_collective_members', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->comment("nama yang di insertkan di excell");
            $table->integer("excell_collective_id");
            $table->string("label_category");
            $table->integer("category_id");
            $table->integer("city_id");
            $table->string("label_city");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('excell_collective_members');
    }
}
