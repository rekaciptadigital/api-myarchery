<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $status = [
        0 => "menunggu Pembayaran",
        1 => "Selesai",
        2 => "Kadarluarsa",
        3 => "gagal",
    ];

    protected function getStatus($status){
        return isset($this->status[$status]) ? $this->status[$status] : "none"; 
    }
}
