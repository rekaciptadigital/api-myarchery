<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentClassificationMembers extends Model
{
    use SoftDeletes;

    protected $appends = [
        'detail_admin'
    ];

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s'
    ];

    public function getDetailAdminAttribute()
    {
        $response = [];
        $admin = Admin::find($this->admin_id);

        if ($admin) {
            $response["id"] = $admin->id;
            $response["name"] = $admin->name;
            $response["email"] = $admin->email;
            $response["avatar"] = $admin->avatar;
            $response["phone_number"] = $admin->phone_number;
        }

        return $this->attributes['detail_admin'] = $response;
    }
}
