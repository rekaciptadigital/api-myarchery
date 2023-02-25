<?php

namespace App\Models;

use App\Models\User;
use App\Models\ParentClassificationMembers;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChildrenClassificationMembers extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = [
        'detail_admin', 'detail_user', 'detail_parent'
    ];

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

    public function getDetailUserAttribute()
    {
        $response = [];
        $user = User::find($this->user_id);

        if ($user) {
            $response["id"] = $user->id;
            $response["name"] = $user->name;
            $response["email"] = $user->email;
            $response["avatar"] = $user->avatar;
            $response["phone_number"] = $user->phone_number;
        }

        return $this->attributes['detail_user'] = $response;
    }

    public function getDetailParentAttribute()
    {
        $response = [];
        $parent = ParentClassificationMembers::find($this->parent_id);

        if ($parent) {
            $response["id"] = $parent->id;
            $response["title"] = $parent->title;
        }

        return $this->attributes['parent_detail'] = $response;
    }
}
