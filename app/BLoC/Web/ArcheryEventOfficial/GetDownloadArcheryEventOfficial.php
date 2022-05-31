<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\User;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventOfficialTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;

use Illuminate\Support\Facades\Storage;
use Knp\Snappy\Pdf;

class GetDownloadArcheryEventOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $admin = Auth::user();

        $official_id = $parameters->get('official_id');
        $event_id = $parameters->get('event_id');

        $template = ArcheryEventOfficialTemplate::where('event_id', $event_id)->first();

        if (!$template) {
            throw new BLoCException("template official was not found");
        }

        $category = 'Official';

        if (!$official_id) {
            $allOfficialParticipant = ArcheryEventOfficial::select('*')
                ->leftJoin('archery_event_official_detail', 'archery_event_official_detail.id', 'archery_event_official.event_official_detail_id')
                ->where("event_id", $event_id)->get();

            if ($allOfficialParticipant->isEmpty()) {
                throw new BLoCException("data was not found");
            }
            $file_name = "idcard_official" . $event_id . ".pdf";
            foreach ($allOfficialParticipant as $official) {
                $final_doc[] = $this->generate($official, $event_id);
            }
            $generate_idcard = PdfLibrary::setArrayDoc($final_doc)->setFileName($file_name)->setOfficial($category)->generateIdcard();
            return [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];
        } else {
            $official = ArcheryEventOfficial::find($official_id);
            if (!$official) {
                throw new BLoCException("data was not found");
            }

            $final_doc = $this->generate($official, $event_id);
            $file_name = "idcard_official" . $official->user_id . ".pdf";
            $generate_idcard = PdfLibrary::setFinalDoc($final_doc)->setFileName($file_name)->setOfficial($category)->generateIdcard();

            return [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];
        }
    }
    protected function validation($parameters)
    {
        return [
            'event_id' => [
                'required'
            ],

        ];
    }

    protected function generate($official, $event_id)
    {
        if ($official->club_id) {
            $club_find = ArcheryClub::find($official->club_id);
            if (!$club_find) {
                $club = '';
            } else {
                $club = $club_find->name;
            }
        } else {
            $club = '';
        }

        $user = User::find($official->user_id);
        if (!$user) {
            throw new BLoCException("data was not found");
        }

        $template = ArcheryEventOfficialTemplate::where('event_id', $event_id)->first();
        if (!$template) {
            throw new BLoCException("template official was not found");
        }

        if (!$template->background) {
            $background = '';
        } else {
            $background = 'background:url("' . $template->background . '")';
        }

        if (!$template->logo_event) {
            $logo = '<div id="logo" style="padding:3px"></div>';
        } else {
            $logo = '<img src="' . $template->logo_event . '" alt="Avatar" style="float:left;width:40px">';
        }

        $html_template = base64_decode($template->html_template);
        $final_doc = str_replace(
            ['{%foreground%}', "background:url('')", '<div></div>', '{%nama_peserta%}', '{%club%}'],
            [$template->foreground, $background, $logo, $user->name, $club],
            $html_template
        );

        return $final_doc;
    }
}
