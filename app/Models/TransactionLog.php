<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $status = [
        0 => "waiting_payment",
        1 => "settlement",
        2 => "expire",
        3 => "failure",
    ];
}
