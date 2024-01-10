<?php

namespace App\BLoC\General\Certificate;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMemberCertificate;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class BulkDownloadWinerCertificateByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // parameter
        $category_id = $parameters->get("category_id");

        $category = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_master_competition_categories.label as label_competition",
            "archery_master_distances.label as label_distance",
            "archery_master_age_categories.label as label_age",
            "archery_master_team_categories.label as label_team"
        )->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_category_details.competition_category_id")
            ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_category_details.distance_id")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();
        if (!$category) {
            throw new BLoCException("category tidak ditemukan");
        }

        $output = [];

        if ($category->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            $certificate = ArcheryMemberCertificate::bulkPrepareUserCertificateByCategoryIndividu($category);
        } else {
            $certificate = ArcheryMemberCertificate::bulkPrepareUserCertificateByCategoryTeam($category);
        }
        if (!empty($certificate)) {
            $output[] = [
                "event_id" => $category->event_id,
                "event_name" => $category->event_name,
                "certificates" => $certificate
            ];
        }


        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "category_id" => "required|integer|exists:archery_event_category_details,id",
            "type" => "in:Individual,Team"
        ];
    }
}
