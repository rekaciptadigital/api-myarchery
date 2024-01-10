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

        $category = ArcheryEventCategoryDetail::find($category_id);
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
