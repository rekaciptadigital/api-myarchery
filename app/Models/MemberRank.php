<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberRank extends Model
{
    protected $table = 'member_rank';

    public static function updateMemberRank(ArcheryEventCategoryDetail $category)
    {
        $sessions = $category->getArraySessionCategory();
        $list_member_rank = ArcheryScoring::getScoringRankByCategoryId($category->id, 1, $sessions, false, null, false);
        foreach ($list_member_rank as $key => $value) {
            $member_id = $value["member"]->id;
            $member_rank = MemberRank::where("member_id", $member_id)->where("category_id", $category->id)->first();
            if (!$member_rank) {
                $member_rank = new MemberRank();
            }

            $member_rank->category_id = $category->id;
            $member_rank->member_id = $member_id;
            $member_rank->rank = $key + 1;
            $member_rank->save();
        }
    }
}
