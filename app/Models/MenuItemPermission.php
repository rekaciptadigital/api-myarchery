<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemPermission extends Model
{
    protected $fillable = [
        'menu_item_id',
        'permission_id',
    ];
}
