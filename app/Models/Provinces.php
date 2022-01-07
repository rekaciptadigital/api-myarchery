<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provinces extends Model
{
    protected $table = 'provinces';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = "char";
}
