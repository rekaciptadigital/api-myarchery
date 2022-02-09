<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\BudRest;
use App\Models\ParticipantMemberTeam;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetBudRest extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException('event not found');
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $output = [];
        $list_competition_category = ArcheryMasterCompetitionCategory::all();
        if ($list_competition_category->count() > 0) {
            foreach ($list_competition_category as $competition_category) {
                $list_category = ArcheryEventCategoryDetail::join('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_event_category_details.team_category_id')
                    ->join('archery_event_qualification_time', 'archery_event_qualification_time.category_detail_id', '=', 'archery_event_category_details.id')
                    ->where('archery_event_category_details.event_id', $event->id)
                    ->where('archery_event_category_details.competition_category_id', $competition_category->id)
                    ->where('archery_master_team_categories.type', 'Individual')
                    ->distinct()
                    ->get(['archery_event_category_details.*']);

                if ($list_category->count() > 0) {
                    foreach ($list_category as $category) {
                        $detail_category = $category->getCategoryDetailById($category->id);
                        $detail_category['bud_rest'] = BudRest::where('archery_event_category_id', $category->id)->first();
                        $detail_category['total_participant'] = ParticipantMemberTeam::where('event_category_id', $category->id)->get()->count();
                        $start_event = ArcheryEventQualificationTime::where('category_detail_id', $category->id)->first();
                        $detail_category['qualification_start'] = $start_event ? $start_event->event_start_datetime : null;
                        $output[$competition_category->label][] = $detail_category;
                    }
                }
            }
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer',
        ];
    }
}
