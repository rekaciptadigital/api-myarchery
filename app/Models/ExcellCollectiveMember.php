<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcellCollectiveMember extends Model
{
    protected $table = 'excell_collective_members';
    protected $guarded = ["id"];

    public static function saveExcellCollectiveMember(string $member_name, int $city_id, string $city_name, int $category_id, int $excellCollective_id, string $category_label)
    {
        $excellCollectiveMember = new ExcellCollectiveMember();
        $excellCollectiveMember->name = $member_name;
        $excellCollectiveMember->excell_collective_id = $excellCollective_id;
        $excellCollectiveMember->label_category = $category_label;
        $excellCollectiveMember->category_id = $category_id;
        $excellCollectiveMember->city_id = $city_id;
        $excellCollectiveMember->label_city = $city_name;
        $excellCollectiveMember->save();

        return $excellCollectiveMember;
    }
}
