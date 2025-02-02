<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCertificateTemplates;
use App\Libraries\PdfLibrary;
use App\Models\User;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Log;

class ArcheryMemberCertificate extends Model
{
    protected $fillable = [
        'id',
        'member_id',
        'certificate_template_id',
    ];

    protected $replace_item_by_certificate_type = [
        "{%member_name%}" => "",
        "{%category_name%}" => "",
        "{%ranked%}" => "",
        "{%certificate_verify_url%}" => "",
        "{%background%}" => ""
    ];

    private static $replace_item_by_certificate_type_2 = [
        "{%member_name%}" => "",
        "{%category_name%}" => "",
        "{%ranked%}" => "",
        "{%certificate_verify_url%}" => "",
        "{%background%}" => ""
    ];

    protected function prepareUserCertificate($event_id, $user_id)
    {
        $certificate_templates = ArcheryEventCertificateTemplates::where("event_id", $event_id)->get();
        $certificates = [];
        $user_certificates = [];
        $user_certificate_by_categories = [];

        $members = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participants.event_category_id",
            "archery_event_participants.club_id",
            "archery_event_participants.competition_category_id",
            "archery_event_participants.distance_id",
            "archery_event_participants.team_category_id",
            "archery_event_participants.age_category_id"
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.event_id", $event_id)
            ->where("archery_event_participants.type", "individual")
            ->where("archery_event_participant_members.user_id", $user_id)
            ->where("archery_event_participants.status", 1)
            ->get();
        $user = User::find($user_id);

        $item = collect($this->replace_item_by_certificate_type);

        $item["{%member_name%}"] = strtoupper($user->name);
        foreach ($members as $key => $value) {
            $category = ArcheryEventCategoryDetail::getCategoryLabelComplete($value->event_category_id);
            $category_detail = ArcheryEventCategoryDetail::find($value->event_category_id);
            $elimination_member = ArcheryEventEliminationMember::where("member_id", $value->id)->first();
            $item["{%category_name%}"] = $category;
            $files = [];
            foreach ($certificate_templates as $c => $template) {
                $type_certificate = $template->type_certificate;
                $html_template_with_masking = collect($template->html_template);

                $type_certificate_label = ArcheryEventCertificateTemplates::getCertificateLabelByType($type_certificate);
                if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("participant")) {
                } elseif ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("winner")) {
                    if (!$elimination_member || $elimination_member->elimination_ranked < 1 || $elimination_member->elimination_ranked > 3) {
                        continue;
                    }
                    $item["{%ranked%}"] = $elimination_member->elimination_ranked;
                } elseif ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("elimination")) {
                    if (!$elimination_member) {
                        continue;
                    }
                } elseif ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("qualification_winner")) {
                    if (!$elimination_member || $elimination_member->position_qualification < 1 || $elimination_member->position_qualification > 3) {
                        continue;
                    }
                    $item["{%ranked%}"] = $elimination_member->position_qualification;
                } elseif ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("team_qualification_winner")) {
                    if ($value->club_id == 0) {
                        continue;
                    }
                    $team_category = $value->team_category_id == "individu female" ? "female_team" : "male_team";

                    // dapatkan elimination group yang satu grup dengan category individu
                    $category_team = ArcheryEventCategoryDetail::where("competition_category_id", $value->competition_category_id)
                        ->where("age_category_id", $value->age_category_id)
                        ->where("distance_id", $value->distance_id)
                        ->where("team_category_id", $team_category)
                        ->where("event_id", $event_id)
                        ->first();

                    $rank = 0;
                    if ($category_team) {
                        $item["{%category_name%}"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_team->id);
                        $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
                        if ($elimination_group) {
                            $group_member_team =  ArcheryEventEliminationGroupMemberTeam::where("member_id", $value->id)->first();
                            if (!$group_member_team) {
                                continue;
                            }

                            $elimination_group_team = ArcheryEventEliminationGroupTeams::where("participant_id", $group_member_team->participant_id)->first();
                            if (!$elimination_group_team) {
                                continue;
                            }

                            if ($elimination_group_team->elimination_ranked > 3 || $elimination_group_team->elimination_ranked < 1) {
                                continue;
                            }

                            $type_certificate_label = "Eliminasi Beregu";
                            $rank = $elimination_group_team->elimination_ranked;
                        } else {
                            $team_participant = ArcheryEventParticipant::select("archery_event_participants.event_category_id")
                                ->where("archery_event_participants.type", "team")
                                ->where("archery_event_participants.event_id", $event_id)
                                ->where("archery_event_participants.competition_category_id", $value->competition_category_id)
                                ->where("archery_event_participants.distance_id", $value->distance_id)
                                ->where("archery_event_participants.age_category_id", $value->age_category_id)
                                ->where("archery_event_participants.club_id", $value->club_id)
                                ->where("archery_event_participants.team_category_id", $team_category)
                                ->where("archery_event_participants.status", 1)
                                ->groupBy("archery_event_participants.event_category_id")
                                ->get();



                            foreach ($team_participant as $tp => $team) {
                                $team_category_detail = ArcheryEventCategoryDetail::find($team->event_category_id);
                                if ($team_category_detail->qualification_mode == "best_of_three") {
                                    $team_score = ArcheryEventParticipant::teamBestOfThree($team_category_detail);
                                    foreach ($team_score as $ts => $score) {
                                        $matching = false;
                                        if ($ts >= 3) {
                                            break;
                                        }
                                        foreach ($score["teams"] as $t => $team) {
                                            if ($team->id == $value->id) {
                                                $rank = $ts + 1;
                                                $matching = true;
                                                break;
                                            }
                                        }
                                        if ($matching) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        continue;
                    }

                    if ($rank == 0) {
                        continue;
                    }


                    $item["{%ranked%}"] = $rank;
                } elseif ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("mix_team_qualification_winner")) {
                    if ($value->club_id == 0) {
                        continue;
                    }
                    $team_category = "mix_team";

                    // dapatkan elimination group yang satu grup dengan category individu
                    $category_team = ArcheryEventCategoryDetail::where("competition_category_id", $value->competition_category_id)
                        ->where("age_category_id", $value->age_category_id)
                        ->where("distance_id", $value->distance_id)
                        ->where("team_category_id", $team_category)
                        ->where("event_id", $event_id)
                        ->first();

                    $rank = 0;

                    if ($category_team) {
                        $item["{%category_name%}"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_team->id);
                        $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
                        if ($elimination_group) {
                            $group_member_team =  ArcheryEventEliminationGroupMemberTeam::where("member_id", $value->id)->first();
                            if (!$group_member_team) {
                                continue;
                            }

                            $elimination_group_team = ArcheryEventEliminationGroupTeams::where("participant_id", $group_member_team->participant_id)->first();
                            if (!$elimination_group_team) {
                                continue;
                            }

                            if ($elimination_group_team->elimination_ranked > 3 || $elimination_group_team->elimination_ranked < 1) {
                                continue;
                            }

                            $type_certificate_label = "Eliminasi Beregu Campuran";
                            $rank = $elimination_group_team->elimination_ranked;
                        } else {
                            $team_participant = ArcheryEventParticipant::select("archery_event_participants.event_category_id")
                                ->where("archery_event_participants.type", "team")
                                ->where("archery_event_participants.event_id", $event_id)
                                ->where("archery_event_participants.competition_category_id", $value->competition_category_id)
                                ->where("archery_event_participants.distance_id", $value->distance_id)
                                ->where("archery_event_participants.age_category_id", $value->age_category_id)
                                ->where("archery_event_participants.club_id", $value->club_id)
                                ->where("archery_event_participants.team_category_id", $team_category)
                                ->where("archery_event_participants.status", 1)
                                ->groupBy("archery_event_participants.event_category_id")
                                ->get();

                            foreach ($team_participant as $tp => $team) {
                                $item["{%category_name%}"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($team->event_category_id);
                                $team_category_detail = ArcheryEventCategoryDetail::find($team->event_category_id);
                                if ($team_category_detail->qualification_mode == "best_of_three") {
                                    $team_score = ArcheryEventParticipant::mixTeamBestOfThree($team_category_detail);
                                    foreach ($team_score as $ts => $score) {
                                        $matching = false;
                                        if ($ts >= 3) {
                                            break;
                                        }
                                        foreach ($score["teams"] as $t => $team) {
                                            if ($team->id == $value->id) {
                                                $rank = $ts + 1;
                                                $matching = true;
                                                break;
                                            }
                                        }
                                        if ($matching) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        continue;
                    }

                    if ($rank == 0) {
                        continue;
                    }
                    $item["{%ranked%}"] = $rank;
                } else {
                    continue;
                }



                $member_certificate_id = $value->id . "-" . $template->id;
                $validate_link = env("WEB_URL") . "/certificate/validate/" . $member_certificate_id;
                $item["{%certificate_verify_url%}"] = $validate_link;
                $item["{%background%}"] = $template->background_url;



                $html_template_clean = "";
                $html_template_clean = base64_decode($html_template_with_masking);

                foreach ($item as $i => $item_detail) {
                    $html_template_clean = str_replace($i, $item_detail, $html_template_clean);
                }

                $member_certificate = $this->find($member_certificate_id);
                if (!$member_certificate) {
                    $member_certificate = $this->create(array(
                        'id' => $member_certificate_id,
                        'member_id' => $value->id,
                        'certificate_template_id' => $template->id,
                    ));
                }

                $path = "asset/certificate/event_" . $event_id;
                if (!file_exists(public_path() . "/" . $path)) {
                    mkdir(public_path() . "/" . $path, 0775);
                }
                $path = "asset/certificate/event_" . $event_id . "/" . $type_certificate;
                if (!file_exists(public_path() . "/" . $path)) {
                    mkdir(public_path() . "/" . $path, 0775);
                }
                $path = "asset/certificate/event_" . $event_id . "/" . $type_certificate . "/users";
                if (!file_exists(public_path() . "/" . $path)) {
                    mkdir(public_path() . "/" . $path, 0775);
                }

                $path = "asset/certificate/event_" . $event_id . "/" . $type_certificate . "/users/" . $user_id;
                if (!file_exists(public_path() . "/" . $path)) {
                    mkdir(public_path() . "/" . $path, 0775);
                }

                $category_arr = explode(" - ", $category);

                if (count($category_arr) > 3)
                    $category = trim($category_arr[0]) . " - " . trim($category_arr[1]) . " - " . trim($category_arr[2]);
                $file_name = $path . "/" . "[" . $member_certificate_id . "]" . $category . "-" . $type_certificate_label . ".pdf";
                if (!file_exists(public_path() . "/" . $file_name)) {
                    PdfLibrary::setFinalDoc($html_template_clean)->setFileName($file_name)->savePdf();
                }

                $files[] = [
                    "name" => $type_certificate_label,
                    "url" =>  env('APP_HOSTNAME') . $file_name
                ];
            };

            if (!empty($files)) {
                $user_certificate_by_categories[] = [
                    "category" => [
                        "id" => $value->event_category_id,
                        "name" => $category,
                    ],
                    "files" => $files
                ];
            }
        }

        return $user_certificate_by_categories;
    }

    public static function bulkPrepareUserCertificateByCategoryIndividu(ArcheryEventCategoryDetail $category_individu, $type = "winner")
    {
        $certificate_templates = ArcheryEventCertificateTemplates::where("event_id", $category_individu->event_id)->get();

        $members = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name as member_name",
            "archery_event_participants.event_category_id",
            "archery_event_participants.club_id",
            "archery_event_participants.competition_category_id",
            "archery_event_participants.distance_id",
            "archery_event_participants.team_category_id",
            "archery_event_participants.age_category_id",
            "archery_master_competition_categories.label as label_competition",
            "archery_master_age_categories.label as label_age",
            "archery_master_distances.label as label_distance",
            "archery_master_team_categories.label as label_team",
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_participants.competition_category_id")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_participants.age_category_id")
            ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_participants.distance_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_participants.team_category_id")
            ->where("archery_event_participants.event_category_id", $category_individu->id)
            ->where("archery_event_participants.status", 1)
            ->get();


        $item = collect(self::$replace_item_by_certificate_type_2);

        $array_doc = [];

        foreach ($members as $key => $value) {
            $elimination_member = ArcheryEventEliminationMember::where("member_id", $value->id)->first();
            $files = [];

            foreach ($certificate_templates as $c => $template) {
                $type_certificate = $template->type_certificate;
                $html_template_with_masking = collect($template->html_template);

                $category = "";


                if ($type == "participant") {
                    if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("participant")) {
                        $category = "Peserta - " . $value->label_competition . " " . $value->label_age . " " . $value->label_distance . " - " . $value->label_team;
                        $item["{%member_name%}"] = strtoupper($value->member_name);
                    }
                }

                if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("elimination")) {
                    continue;
                }

                if ($type == "winner") {
                    if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("winner")) {
                        if (!$elimination_member || $elimination_member->elimination_ranked < 1 || $elimination_member->elimination_ranked > 3) {
                            continue;
                        }
                        $item["{%ranked%}"] = $elimination_member->elimination_ranked;
                        $category = "Juara " . $elimination_member->elimination_ranked . " Eliminasi - " . $value->label_competition . " " . $value->label_age . " " . $value->label_distance . " - " . $value->label_team;
                        $item["{%member_name%}"] = strtoupper($value->member_name);
                    }

                    if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("qualification_winner")) {
                        if (!$elimination_member || $elimination_member->position_qualification < 1 || $elimination_member->position_qualification > 3) {
                            continue;
                        }
                        $item["{%ranked%}"] = $elimination_member->position_qualification;
                        $category = "Juara " . $elimination_member->position_qualification . " Kualifikasi - " . $value->label_competition . " " . $value->label_age . " " . $value->label_distance . " - " . $value->label_team;
                        $item["{%member_name%}"] = strtoupper($value->member_name);
                    }
                }


                if (empty($category)) {
                    continue;
                }

                $item["{%category_name%}"] = strtoupper($category);

                $member_certificate_id = $value->id . "-" . $template->id;
                $validate_link = env("WEB_URL") . "/certificate/validate/" . $member_certificate_id;
                $item["{%certificate_verify_url%}"] = $validate_link;
                $item["{%background%}"] = $template->background_url;

                $html_template_clean = "";
                $html_template_clean = base64_decode($html_template_with_masking);

                foreach ($item as $i => $item_detail) {
                    $html_template_clean = str_replace($i, $item_detail, $html_template_clean);
                }


                $array_doc[] = $html_template_clean;
            };
        }

        $path = "asset/certificate/event_" . $category_individu->event_id . "/category";
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0775);
        }

        $file_name = $path . "/" . $category_individu->id . "_" . $type . ".pdf";

        PdfLibrary::setArrayDoc($array_doc)->setFileName($file_name)->savePdf();

        $files = [
            "name" => "",
            "url" =>  env('APP_HOSTNAME') . $file_name
        ];

        return $files;
    }

    public static function bulkPrepareUserCertificateByCategoryTeam(ArcheryEventCategoryDetail $category_team)
    {
        $certificate_templates = ArcheryEventCertificateTemplates::where("event_id", $category_team->event_id)->get();
        $user_certificate_by_categories = [];

        $item = collect(self::$replace_item_by_certificate_type_2);

        $array_doc = [];
        $list_data_document = [];
        foreach ($certificate_templates as $c => $template) {
            $type_certificate = $template->type_certificate;
            $html_template_with_masking = collect($template->html_template);

            if ($category_team->team_category_id != "mix_team") {
                if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("team_qualification_winner")) {
                    $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
                    if ($elimination_group) {
                        $elimination_group_teams =  ArcheryEventEliminationGroupTeams::join(
                            "archery_event_participants",
                            "archery_event_participants.id",
                            "=",
                            "archery_event_elimination_group_teams.participant_id"
                        )->where("archery_event_participants.event_category_id", $category_team->id)
                            ->where("archery_event_elimination_group_teams.elimination_ranked", ">=", 1)->where("archery_event_elimination_group_teams.elimination_ranked", "<=", 3)
                            ->get();

                        foreach ($elimination_group_teams as $key => $egt) {
                            $group_member_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $egt->participant_id)->get();
                            foreach ($group_member_team as $key => $gmt) {
                                $member = ArcheryEventParticipantMember::find($gmt->member_id);
                                if (!$member) {
                                    continue;
                                }
                                $list_data_document[] = [
                                    "member_name" => strtoupper($member->name),
                                    "rank" => $egt->elimination_ranked,
                                    "label" => strtoupper("Juara " . $egt->elimination_ranked . " Eliminasi - " . $category_team->label_competition . " " . $category_team->label_age . " " . $category_team->label_distance . " - " . $category_team->label_team),
                                    "background" => $template->background_url,
                                    "html_template_with_masking" => $html_template_with_masking
                                ];
                            }
                        }
                    } else {
                        $list_score_team = ArcheryEventParticipant::teamBestOfThree($category_team);

                        foreach ($list_score_team as $key_1 => $lst) {
                            if ($key_1 > 2) {
                                break;
                            }

                            $rank = $key_1 + 1;
                            foreach ($lst["teams"] as $key => $lst_value) {
                                $list_data_document[] = [
                                    "member_name" => strtoupper($lst_value["name"]),
                                    "rank" => $rank,
                                    "label" => strtoupper("Juara " . $rank . " Kualifikasi - " . $category_team->label_competition . " " . $category_team->label_age . " " . $category_team->label_distance . " - " . $category_team->label_team),
                                    "background" => $template->background_url,
                                    "html_template_with_masking" => $html_template_with_masking
                                ];
                            }
                        }
                    }
                }
            }

            if ($category_team->team_category_id == "mix_team") {
                if ($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("mix_team_qualification_winner")) {
                    $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
                    if ($elimination_group) {
                        $elimination_group_teams =  ArcheryEventEliminationGroupTeams::join(
                            "archery_event_participants",
                            "archery_event_participants.id",
                            "=",
                            "archery_event_elimination_group_teams.participant_id"
                        )->where("archery_event_participants.event_category_id", $category_team->id)
                            ->where("archery_event_elimination_group_teams.elimination_ranked", ">=", 1)->where("archery_event_elimination_group_teams.elimination_ranked", "<=", 3)
                            ->get();

                        foreach ($elimination_group_teams as $key => $egt) {
                            $group_member_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $egt->participant_id)->get();
                            foreach ($group_member_team as $key => $gmt) {
                                $member = ArcheryEventParticipantMember::find($gmt->member_id);
                                if (!$member) {
                                    continue;
                                }
                                $list_data_document[] = [
                                    "member_name" => strtoupper($member->name),
                                    "rank" => $egt->elimination_ranked,
                                    "label" => strtoupper("Juara " . $egt->elimination_ranked . " Eliminasi - " . $category_team->label_competition . " " . $category_team->label_age . " " . $category_team->label_distance . " - " . $category_team->label_team),
                                    "background" => $template->background_url,
                                    "html_template_with_masking" => $html_template_with_masking
                                ];
                            }
                        }
                    } else {
                        $list_score_team = ArcheryEventParticipant::mixTeamBestOfThree($category_team);

                        foreach ($list_score_team as $key_1 => $lst) {
                            if ($key_1 > 2) {
                                break;
                            }

                            $rank = $key_1 + 1;
                            foreach ($lst["teams"] as $key => $lst_value) {
                                $list_data_document[] = [
                                    "member_name" => strtoupper($lst_value["name"]),
                                    "rank" => $rank,
                                    "label" => strtoupper("Juara " . $rank . " Kualifikasi - " . $category_team->label_competition . " " . $category_team->label_age . " " . $category_team->label_distance . " - " . $category_team->label_team),
                                    "background" => $template->background_url,
                                    "html_template_with_masking" => $html_template_with_masking
                                ];
                            }
                        }
                    }
                }
            }
        }

        foreach ($list_data_document as $key => $value) {
            $html_template_clean = "";
            $html_template_clean = base64_decode($value["html_template_with_masking"]);

            $item = [];
            $item["{%background%}"] = $value["background"];
            $item["{%member_name%}"] = $value["member_name"];
            $item["{%category_name%}"] = $value["label"];
            $item["{%ranked%}"] = $value["rank"];

            foreach ($item as $i => $item_detail) {
                $html_template_clean = str_replace($i, $item_detail, $html_template_clean);
            }


            $array_doc[] = $html_template_clean;
        }

        $path = "asset/certificate/event_" . $category_team->event_id;
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0775);
        }

        $file_name = $path . "/" . $category_team->id . ".pdf";

        PdfLibrary::setArrayDoc($array_doc)->setFileName($file_name)->savePdf();

        $files = [
            "name" => "",
            "url" =>  env('APP_HOSTNAME') . $file_name
        ];

        return $files;
    }
}
