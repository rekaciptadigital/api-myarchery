<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsersAddFieldTempatLahirAddressAddressProvinceIdAddressCityId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->text('place_of_birth')->nullable();
            $table->char('address_province_id', 2)->nullable()->index();
            $table->char('address_city_id', 4)->nullable()->index();
            $table->smallInteger('verify_status')->default(4)->comment("4 untuk belum terverifikasi, 3 untuk diajukan, 2 untuk ditolak, 1 untuk terverifikasi");
            $table->date("date_verified")->nullable();
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
