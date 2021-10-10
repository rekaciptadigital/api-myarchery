<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArcheryEventOrganizer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archery_event_organizers', function (Blueprint $table) {
            $table->increments('id');
            $table->string("eo_name",225)->nullable();
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->integer('eo_id')->default(0)->index();
        });
    }

    //INSERT INTO `archery_event_organizers` (`id`, `eo_name`, `created_at`, `updated_at`) VALUES (NULL, 'The Hub', NULL, NULL);
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archery_event_organizers');
        Schema::table('admins', function (Blueprint $table) {
            $table->removeColumn('eo_id');
        });
    }
}
