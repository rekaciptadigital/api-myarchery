<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $status = [
        0 => "Menunggu Pembayaran",
        1 => "Selesai",
        2 => "Kadarluarsa",
        3 => "Gagal",
    ];

    public static function getStatus($status){
        return isset(self::$status[$status]) ? self::$status[$status] : "none"; 
    }
}
