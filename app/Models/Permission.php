<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = [];

    public static function generateFor($table_name)
    {
        $permissions = [];
        $permissions[] = self::firstOrCreate(['key' => 'browse_'.$table_name, 'description' => 'Browse '.$table_name, 'label' => 'Browse '.$table_name]);
        $permissions[] = self::firstOrCreate(['key' => 'read_'.$table_name, 'description' => 'Read '.$table_name, 'label' => 'Read '.$table_name]);
        $permissions[] = self::firstOrCreate(['key' => 'edit_'.$table_name, 'description' => 'Edit '.$table_name, 'label' => 'Edit '.$table_name]);
        $permissions[] = self::firstOrCreate(['key' => 'add_'.$table_name, 'description' => 'Add '.$table_name, 'label' => 'Add '.$table_name]);
        $permissions[] = self::firstOrCreate(['key' => 'delete_'.$table_name, 'description' => 'Delete '.$table_name, 'label' => 'Delete '.$table_name]);

        $administrator = Role::where('name', 'administrator')->firstOrFail();

        if (!is_null($administrator)) {
            foreach ($permissions as $row) {
                $role_permission = RolePermission::where('role_id', $administrator->id)
                        ->where('permission_id', $row->id)
                        ->first();
                if (is_null($role_permission)) {
                    $role_permission = new RolePermission();
                    $role_permission->role_id = $administrator->id;
                    $role_permission->permission_id = $row->id;
                    $role_permission->save();
                }
            }
        }
    }

    public static function removeFrom($table_name)
    {
        $permissions = self::where(['table_name' => $table_name])->get();
        $permissions = collect($permissions)->pluck('id')->toArray();
        RolePermission::whereIn('permission_id', $permissions)->delete();
        self::where(['table_name' => $table_name])->delete();
    }
}
