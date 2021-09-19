<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $status = [
        4 => "Menunggu Pembayaran",
        1 => "Telah Dibayar",
        2 => "Kadarluarsa",
        3 => "Gagal",
    ];

    protected function getStatus($status_id){
        return isset($this->status[$status_id]) ? $this->status[$status_id] : "none"; 
    }
}
