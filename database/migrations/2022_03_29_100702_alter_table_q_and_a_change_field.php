<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableQAndAChangeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_archery_event_q_and_a', function (Blueprint $table) {
            $table->renameColumn('title', 'question');
            $table->renameColumn('description', 'answer');
        });

        Schema::table('table_archery_event_q_and_a', function (Blueprint $table) {
            $table->text("question")->change();
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
