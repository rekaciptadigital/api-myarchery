<?php

namespace App\Models;

use App\Libraries\ClubRanked;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArcheryEventParticipant extends Model
{
  protected $guarded = ['id'];

  public static $user, $unique_id, $team_name,
    $event_category_detail, $status, $club_id;

  public static $total_per_points = [
    "" => 0,
    "1" => 0,
    "2" => 0,
    "3" => 0,
    "4" => 0,
    "5" => 0,
    "6" => 0,
    "7" => 0,
    "8" => 0,
    "9" => 0,
    "10" => 0,
    "11" => 0,
    "12" => 0,
    "x" => 0,
    "m" => 0,
  ];

  public static function getElimination(ArcheryEventCategoryDetail $category_detail)
  {

    $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
    if (!$team_category) {
      throw new BLoCException("team category not found");
    }

    if (strtolower($team_category->type) == "team") {
      $data = ArcheryEventParticipant::getTemplateTeam($category_detail);
    }

    if (strtolower($team_category->type) == "individual") {
      $data = ArcheryEventParticipant::getTemplateIndividu($category_detail);
    }

    return $data;
  }

  public static function getCountParticipantTeamWithSameWithContingent(ArcheryEventCategoryDetail $category_team, ArcheryEvent $event, $classification_data)
  {
    $query_count_participant_team = ArcheryEventParticipant::where("event_id", $event->id)
      ->where("age_category_id", $category_team->age_category_id)
      ->where("competition_category_id", $category_team->competition_category_id)
      ->where("distance_id", $category_team->distance_id)
      ->where("team_category_id", $category_team->team_category_id)
      ->where("status", 1);
    if ($event->with_contingent == 1) {
      if ($event['parent_classification'] == 1) {
        $query_count_participant_team = $query_count_participant_team->where('club_id', '=', $classification_data['club_id']);
      } elseif ($event['parent_classification'] == 2) {
        $query_count_participant_team = $query_count_participant_team->where('classification_country_id', '=', $classification_data['country_id']);
      } elseif ($event['parent_classification'] == 3) {
        $query_count_participant_team = $query_count_participant_team
          ->where('classification_country_id', '=', $classification_data['country_id'])
          ->where('classification_province_id', '=', $classification_data['province_id']);
      } elseif ($event['parent_classification'] == 4) {
        $query_count_participant_team = $query_count_participant_team
          ->where('classification_country_id', '=', $classification_data['country_id'])
          ->where('classification_province_id', '=', $classification_data['province_id'])
          ->where('city_id', '=', $classification_data['city_id']);
      } else {
        $query_count_participant_team = $query_count_participant_team
          ->where('children_classification_id', '=', $classification_data['children_id']);
      }
    }
    // if ($event->with_contingent == 1) {
    //   $count_participant_team_with_same_club_or_city->where("city_id", $club_or_city_id);
    // } else {
    //   $count_participant_team_with_same_club_or_city->where("club_id", $club_or_city_id);
    // }

    // return $query_count_participant_team->toSql();
    $query_count_participant_team = $query_count_participant_team->get()->count();

    return (int)$query_count_participant_team;
  }

  public static function getCountParticipantIndividuByCategoryTeam(ArcheryEventCategoryDetail $category_team, ArcheryEvent $event, int $club_or_city_id, string $team_category_id)
  {
    $count_participant_individu = ArcheryEventParticipant::where("event_id", $event->id)
      ->where("age_category_id", $category_team->age_category_id)
      ->where("competition_category_id", $category_team->competition_category_id)
      ->where("distance_id", $category_team->distance_id)
      ->where("team_category_id", $team_category_id)
      ->where("status", 1);

    if ($event->with_contingent == 1) {
      $count_participant_individu->where("city_id", $club_or_city_id);
    } else {
      $count_participant_individu->where("club_id", $club_or_city_id);
    }

    $count_participant_individu = $count_participant_individu->get()->count();

    return (int)$count_participant_individu;
  }

  public static function getCountParticipantTeamWithSameClubOrCity(ArcheryEventCategoryDetail $category_team, ArcheryEvent $event, int $club_or_city_id)
  {
    $count_participant_team_with_same_club_or_city = ArcheryEventParticipant::where("event_id", $event->id)
      ->where("age_category_id", $category_team->age_category_id)
      ->where("competition_category_id", $category_team->competition_category_id)
      ->where("distance_id", $category_team->distance_id)
      ->where("team_category_id", $category_team->team_category_id)
      ->where("status", 1);
    if ($event->with_contingent == 1) {
      $count_participant_team_with_same_club_or_city->where("city_id", $club_or_city_id);
    } else {
      $count_participant_team_with_same_club_or_city->where("club_id", $club_or_city_id);
    }

    $count_participant_team_with_same_club_or_city = $count_participant_team_with_same_club_or_city->get()->count();

    return (int)$count_participant_team_with_same_club_or_city;
  }


  public static function getCountParticipantIndividuByCategoryTeamContingent(ArcheryEventCategoryDetail $category_team, ArcheryEvent $event, $classification_data, string $team_category_id)
  {
    $count_participant_individu = ArcheryEventParticipant::where("event_id", $event->id)
      ->where("age_category_id", $category_team->age_category_id)
      ->where("competition_category_id", $category_team->competition_category_id)
      ->where("distance_id", $category_team->distance_id)
      ->where("team_category_id", $team_category_id)
      ->where("status", 1);
    if ($event->with_contingent == 1) {
      if ($event['parent_classification'] == 1) {
        $count_participant_individu = $count_participant_individu->where('club_id', '=', $classification_data['club_id']);
      } elseif ($event['parent_classification'] == 2) {
        $count_participant_individu = $count_participant_individu->where('classification_country_id', '=', $classification_data['country_id']);
      } elseif ($event['parent_classification'] == 3) {
        $count_participant_individu = $count_participant_individu
          ->where('classification_country_id', '=', $classification_data['country_id'])
          ->where('classification_province_id', '=', $classification_data['province_id']);
      } elseif ($event['parent_classification'] == 4) {
        $count_participant_individu = $count_participant_individu
          ->where('classification_country_id', '=', $classification_data['country_id'])
          ->where('classification_province_id', '=', $classification_data['province_id'])
          ->where('city_id', '=', $classification_data['city_id']);
      } else {
        $count_participant_individu = $count_participant_individu
          ->where('children_classification_id', '=', $classification_data['children_id']);
      }
    }
    // if ($event->with_contingent == 1) {
    //   $count_participant_individu->where("city_id", $club_or_city_id);
    // } else {
    //   $count_participant_individu->where("club_id", $club_or_city_id);
    // }

    $count_participant_individu = $count_participant_individu->get()->count();
    return $count_participant_individu;
    // return (int)$count_participant_individu;
  }

  // public static function getElimination(ArcheryEventCategoryDetail $category_detail)
  // {
  //   $count_participant_team_with_same_club_or_city = ArcheryEventParticipant::where("event_id", $event->id)
  //     ->where("age_category_id", $category_team->age_category_id)
  //     ->where("competition_category_id", $category_team->competition_category_id)
  //     ->where("distance_id", $category_team->distance_id)
  //     ->where("team_category_id", $category_team->team_category_id)
  //     ->where("status", 1);
  //   if ($event->with_contingent == 1) {
  //     $count_participant_team_with_same_club_or_city->where("city_id", $club_or_city_id);
  //   } else {
  //     $count_participant_team_with_same_club_or_city->where("club_id", $club_or_city_id);
  //   }

  //   $count_participant_team_with_same_club_or_city = $count_participant_team_with_same_club_or_city->get()->count();

  //   return (int)$count_participant_team_with_same_club_or_city;
  // }

  public static function getMedalStanding($event_id)
  {
    $data = ClubRanked::getEventRanked($event_id, 1, null);

    if (count($data) > 0) {
      $title_header = array();
      $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))
        ->where("event_id", $event_id)
        ->orderBy('competition_category_id', 'DESC')
        ->get();

      foreach ($competition_category as $competition) {
        $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id", $event_id)
          ->where("competition_category_id", $competition->competition_category)
          ->orderBy('competition_category_id', 'DESC')
          ->get();

        foreach ($age_category as $age) {
          $master_age_category = ArcheryMasterAgeCategory::find($age->age_category);
          $title_header['category'][$competition->competition_category]['age_category'][$master_age_category->label] = [
            'gold' => null,
            'silver' => null,
            'bronze' => null,
          ];
        }

        // colspan header title
        $count_colspan = [
          'count_colspan' => count($age_category) * 3
        ];
        $count_rowspan = [
          "count_rowspan" => count($age_category)
        ];
        array_push($title_header['category'][$competition->competition_category], $count_colspan, $count_rowspan);
      }

      $result = [];
      $detail_club_with_medal_response = [];
      foreach ($data as $key => $d) {
        $detail_club_with_medal_response["club_name"] = $d["club_name"];
        $detail_club_with_medal_response["contingent_name"] = $d["contingent_name"];
        $detail_club_with_medal_response["total_gold"] = $d["gold"];
        $detail_club_with_medal_response["total_silver"] = $d["silver"];
        $detail_club_with_medal_response["total_bronze"] = $d["bronze"];
        $detail_club_with_medal_response["with_contingent"] = $d["with_contingent"];

        foreach ($competition_category as $competition) {
          $age_category = ArcheryEventCategoryDetail::select("archery_master_age_categories.label as age_category")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->where("event_id", $event_id)
            ->where("competition_category_id", $competition->competition_category)
            ->orderBy('competition_category_id', 'DESC')
            ->get();

          foreach ($age_category as $age) {
            $gold = 0;
            $silver = 0;
            $bronze = 0;

            if (isset($d["detail_medal"]) && isset($d["detail_medal"]["category"]) && isset($d["detail_medal"]["category"][$competition->competition_category][$age->age_category])) {
              $gold += $d["detail_medal"]["category"][$competition->competition_category][$age->age_category]["gold"] ?? 0;
              $silver += $d["detail_medal"]["category"][$competition->competition_category][$age->age_category]["silver"] ?? 0;
              $bronze += $d["detail_medal"]["category"][$competition->competition_category][$age->age_category]["bronze"] ?? 0;
            };

            $detail_club_with_medal_response['category'][$competition->competition_category]['age_category'][$age->age_category] = [
              "gold" => $gold,
              "silver" => $silver,
              "bronze" => $bronze
            ];
          }
        }
        $medal_array = [];
        foreach ($detail_club_with_medal_response["category"] as $c) {
          foreach ($c as $a) {
            foreach ($a as $s) {
              foreach ($s as $b) {
                array_push($medal_array, $b);
              }
            }
          }
        }
        $detail_club_with_medal_response["medal_array"] = $medal_array;
        array_push($result, $detail_club_with_medal_response);
      }


      // start: total medal emas, perak, perunggu dari setiap kategori semua klub
      $array_of_total_medal_by_category = [];
      $total_array_category = count($result[0]['medal_array']);
      for ($i = 0; $i < $total_array_category; $i++) {
        $total_medal_by_category = 0;
        for ($j = 0; $j < count($result); $j++) {
          $total_medal_by_category += $result[$j]['medal_array'][$i];
        }
        array_push($array_of_total_medal_by_category, $total_medal_by_category);
      }
      // end: total medal emas, perak, perunggu dari setiap kategori semua klub

      // start: total medal emas, perak, perunggu secara keseluruhan dari semua klub
      $array_of_total_medal_by_category_all_club = [];
      $total_medal_by_category_gold = 0;
      $total_medal_by_category_silver = 0;
      $total_medal_by_category_bronze = 0;
      for ($k = 0; $k < count($result); $k++) {
        $total_medal_by_category_gold += $result[$k]['total_gold'];
        $total_medal_by_category_silver += $result[$k]['total_silver'];
        $total_medal_by_category_bronze += $result[$k]['total_bronze'];
      }
      $array_of_total_medal_by_category_all_club = [
        'gold' => $total_medal_by_category_gold,
        'silver' => $total_medal_by_category_silver,
        'bronze' => $total_medal_by_category_bronze
      ];
      // end: total medal emas, perak, perunggu secara keseluruhan dari semua klub 

      $response = [
        'title_header' => $title_header,
        'datatable' => $result,
        'total_medal_by_category' => $array_of_total_medal_by_category,
        'total_medal_by_category_all_club' => $array_of_total_medal_by_category_all_club
      ];

      return $response;
    } else {
      return [];
    }
  }

  // save participant
  public static function saveArcheryEventParticipant(
    User $user,
    ArcheryEventCategoryDetail $category,
    string $type,
    int $transaction_log_id = 0,
    string $unique_id,
    string $qualification_date = null,
    string $team_name = null,
    int $status,
    $club_id = 0,
    string $reason_refund = null,
    string $upload_image_refund = null,
    int $is_present = 1,
    int $register_by = 1,
    string $day_choice = null,
    int $expired_booking_time = 0,
    int $is_early_bird_payment = 0,
    int $is_special_team_member = 0,
    $city_id = 0,
    int $order_event_id,
    $classification_country_id = 0,
    $classification_province_id = 0,
    $classification_children_id = 0
  ) {
    // return [
    //   'user' => $user,
    //   'category' => $category,
    //   'type' => $type,
    //   'transaction_log_id' => $transaction_log_id,
    //   'unique_id' => $unique_id,
    //   'qualification_date' => $qualification_date,
    //   'team_name' => $team_name,
    //   'status' => $status,
    //   'club_id' => $club_id,
    //   'reason_refund' => $reason_refund,
    //   'upload_image_refund' => $upload_image_refund,
    //   'is_present' => $is_present,
    //   'register_by' => $register_by,
    //   'day_choice' => $day_choice,
    //   'expired_booking_time' => $expired_booking_time,
    //   'is_early_bird_payment' => $is_early_bird_payment,
    //   'is_special_team_member' => $is_special_team_member,
    //   'city_id' => $city_id,
    //   'order_event_id' => $order_event_id,
    //   'classification_country_id' => $classification_country_id,
    //   'classification_province_id' => $classification_province_id,
    //   'classification_children_id' => $classification_children_id,
    // ];
    $participant = new ArcheryEventParticipant();
    $participant->event_id = $category->event_id;
    $participant->user_id = $user->id;
    $participant->name = $user->name;
    $participant->type = $type;
    $participant->email = $user->email;
    $participant->phone_number = $user->phone_number;
    $participant->age = $user->age;
    $participant->gender = $user->gender;
    $participant->team_category_id = $category->team_category_id;
    $participant->age_category_id = $category->age_category_id;
    $participant->competition_category_id = $category->competition_category_id;
    $participant->distance_id = $category->distance_id;
    $participant->transaction_log_id = $transaction_log_id;
    $participant->unique_id = $unique_id;
    $participant->qualification_date = $qualification_date;
    $participant->team_name = $team_name;
    $participant->event_category_id = $category->id;
    $participant->status = $status;
    $participant->club_id = $club_id;
    $participant->reason_refund = $reason_refund;
    $participant->upload_image_refund = $upload_image_refund;
    $participant->is_present = $is_present;
    $participant->register_by = $register_by;
    $participant->day_choice = $day_choice;
    $participant->expired_booking_time = $expired_booking_time;
    $participant->is_early_bird_payment = $is_early_bird_payment;
    $participant->is_special_team_member = $is_special_team_member;
    $participant->city_id = $city_id;
    $participant->order_event_id = $order_event_id;
    $participant->classification_country_id = $classification_country_id;
    $participant->classification_province_id = $classification_province_id;
    $participant->children_classification_id = $classification_children_id;
    $participant->save();

    return $participant;
  }

  public function archeryEventParticipantMembers()
  {
    return $this->hasMany(ArcheryEventParticipantMember::class, 'archery_event_participant_id', 'id');
  }

  public static function getMemberByUserId($user_id, $participant_id)
  {
    $archery_participant = DB::table('archery_event_participants')
      ->select('archery_event_participant_members.*', 'archery_event_participants.event_id', 'archery_event_participants.event_category_id')
      ->join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->where('archery_event_participant_members.user_id', $user_id)
      ->where('archery_event_participants.id', $participant_id)
      ->where('archery_event_participants.status', 1)
      ->first();
    return $archery_participant;
  }


  public static function getTotalPartisipantByEventByCategory($category_detail_id)
  {
    $count_participant = ArcheryEventParticipant::select(DB::raw("count(if(archery_event_participants.status=1,1,if(FROM_UNIXTIME(transaction_logs.expired_time)>=now(),1,NULL))) as total "))
      ->where('event_category_id', $category_detail_id)
      ->leftJoin('transaction_logs', 'transaction_logs.id', 'archery_event_participants.transaction_log_id')
      ->get();
    foreach (array($count_participant) as $key => $count) {
      $total = $count[0]['total'];
    }

    return $total;
  }

  public static function checkParticipantMixteamOrder($event_id, $age_category_id, $competition_category_id, $distance_id, $club_id, $count_participant_same_category)
  {
    $check_individu_category_detail_male = ArcheryEventCategoryDetail::where('event_id', $event_id)
      ->where('age_category_id', $age_category_id)
      ->where('competition_category_id', $competition_category_id)
      ->where('distance_id', $distance_id)
      ->where('team_category_id', "individu male")
      ->first();

    $check_individu_category_detail_female = ArcheryEventCategoryDetail::where('event_id', $event_id)
      ->where('age_category_id', $age_category_id)
      ->where('competition_category_id', $competition_category_id)
      ->where('distance_id', $distance_id)
      ->where('team_category_id', "individu female")
      ->first();

    if (!$check_individu_category_detail_male || !$check_individu_category_detail_female) {
      throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
    }

    $check_participant_male = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->where("archery_event_participants.status", 1)
      ->where('archery_event_participants.event_category_id', $check_individu_category_detail_male->id)
      ->where('archery_event_participants.club_id', $club_id)
      ->count();

    $check_participant_female = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->where("archery_event_participants.status", 1)
      ->where('archery_event_participants.event_category_id', $check_individu_category_detail_female->id)
      ->where('archery_event_participants.club_id', $club_id)
      ->count();

    $message_error = "untuk pendaftaran ke " . ($count_participant_same_category + 1) . " minimal harus ada " . (($count_participant_same_category + 1) * 1) . " peserta laki-laki dan peserta perempuan tedaftar dengan club ini pada kategori individu";

    if ($check_participant_male < (($count_participant_same_category + 1) * 1)) {
      throw new BLoCException($message_error);
    }

    if ($check_participant_female < (($count_participant_same_category + 1) * 1)) {
      throw new BLoCException($message_error);
    }
  }

  public static function getTotalPartisipantEventByStatus($category_detail_id, $status = 0)
  {
    return ArcheryEventParticipant::select("archery_event_participants.*", "transaction_logs.order_id", "archery_event_participants.status", "transaction_logs.expired_time")
      ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
      ->where('archery_event_participants.event_category_id', $category_detail_id)
      ->where(function ($query) use ($status) {
        if (!is_null($status) && $status != 0) {
          $query->where('archery_event_participants.status', $status);
          if ($status == 2) {
            $query->orWhere(function ($query) use ($status) {
              $query->where("transaction_logs.status", 4);
              $query->where("transaction_logs.expired_time", "<=", time());
            });
          }
          if ($status == 1) {
            $query->orWhere(function ($query) use ($status) {
              $query->where("archery_event_participants.status", 1);
            });
          }
          if ($status == 4) {
            $query->where("transaction_logs.expired_time", ">=", time());
          }
        }
      })
      ->count();
  }

  public static function countEventUserBooking($event_category_detail_id)
  {
    $time_now = time();

    return ArcheryEventParticipant::leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
      ->where("event_category_id", $event_category_detail_id)
      ->where(function ($query) use ($time_now) {
        $query->where("archery_event_participants.status", 1);
        $query->orWhere(function ($q) use ($time_now) {
          $q->where("archery_event_participants.status", 4);
          $q->where("transaction_logs.status", 4);
          $q->where("transaction_logs.expired_time", ">", $time_now);
        });
        $query->orWhere(function ($q) use ($time_now) {
          $q->where("archery_event_participants.status", 6);
          $q->where("archery_event_participants.expired_booking_time", ">", $time_now);
        });
      })->count();
  }

  public static function insertParticipant(
    $user,
    $unique_id,
    $event_category_detail,
    $status,
    $club_id,
    $day_choice,
    $expired_booking_time = 0,
    $is_early_bird_payment = 0,
    $city_id = 0,
    $country_id = 0,
    $province_id = 0,
    $children_classification_id = 0
  ) {
    return self::create([
      'club_id' => $club_id,
      'user_id' => $user->id,
      'status' => $status,
      'event_id' => $event_category_detail->event_id,
      'name' => $user->name,
      'type' => $event_category_detail->category_team,
      'email' => $user->email,
      'phone_number' => $user->phone_number,
      'age' => $user->age,
      'gender' => $user->gender,
      'team_category_id' => $event_category_detail->team_category_id,
      'age_category_id' => $event_category_detail->age_category_id,
      'competition_category_id' => $event_category_detail->competition_category_id,
      'distance_id' => $event_category_detail->distance_id,
      'transaction_log_id' => 0,
      'unique_id' => $unique_id,
      'event_category_id' => $event_category_detail->id,
      'day_choice' => $day_choice,
      "expired_booking_time" => $expired_booking_time,
      "is_early_bird_payment" => $is_early_bird_payment,
      'classification_country_id' => $country_id,
      'classification_province_id' => $province_id,
      "city_id" => $city_id,
      'classification_country_id' => $country_id,
      'classification_province_id' => $province_id,
      "children_classification_id" => $children_classification_id
    ]);
  }

  public static function getQualification(ArcheryEventCategoryDetail $category)
  {
    $score_type = 1;
    $name = null;

    $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
    if (!$team_category) {
      throw new BLoCException("team category not found");
    }

    $event = ArcheryEvent::find($category->event_id);
    if (!$event) throw new BLoCException("CATEGORY INVALID");

    $session = [];
    for ($i = 0; $i < $category->session_in_qualification; $i++) {
      $session[] = $i + 1;
    }

    if ($category->category_team == "Individual") {
      $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category->id, $score_type, $session, false, $name, false, 1);

      return $qualification_member;
    }

    if (strtolower($team_category->type) == "team") {
      if ($team_category->id == "mix_team") {
        $data = self::mixTeamBestOfThree($category);
      } else {
        $data = self::teamBestOfThree($category);
      }

      return $data;
    }

    throw new BLoCException("invalid");
  }

  public static function mixTeamBestOfThree(ArcheryEventCategoryDetail $category_detail_team, int $is_live_score = 0)
  {
    $event = ArcheryEvent::find($category_detail_team->event_id);
    if (!$event) {
      throw new BLoCException("event not found");
    }

    $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $category_detail_team->event_id)
      ->where("age_category_id", $category_detail_team->age_category_id)
      ->where("competition_category_id", $category_detail_team->competition_category_id)
      ->where("distance_id", $category_detail_team->distance_id)
      ->where("team_category_id", "individu male")
      ->first();

    if (!$category_detail_male) {
      throw new BLoCException("category detail male not found");
    }

    $session_category_detail_male = $category_detail_male->getArraySessionCategory();

    $qualification_male = ArcheryScoring::getScoringRankByCategoryId($category_detail_male->id, 1, $session_category_detail_male, false, null, false, 1);

    $category_detail_female = ArcheryEventCategoryDetail::where("event_id", $category_detail_team->event_id)
      ->where("age_category_id", $category_detail_team->age_category_id)
      ->where("competition_category_id", $category_detail_team->competition_category_id)
      ->where("distance_id", $category_detail_team->distance_id)
      ->where("team_category_id", "individu female")
      ->first();

    if (!$category_detail_female) {
      throw new BLoCException("category detail female not found");
    }


    $session_category_detail_female = $category_detail_female->getArraySessionCategory();

    $qualification_female = ArcheryScoring::getScoringRankByCategoryId($category_detail_female->id, 1, $session_category_detail_female, false, null, false, 1);

    $participant_club_or_city = [];
    $sequence = [];

    $parent_classfification_id = $event->parent_classification;

    if ($parent_classfification_id == 0) {
      throw new BLoCException("parent calassification_id invalid");
    }

    if ($parent_classfification_id == 5) {
      throw new BLoCException("config not found");
    }

    $tag_ranked = "club_id";
    $select_classification_query = "archery_clubs.name as classification_name";

    if ($parent_classfification_id == 2) { // jika mewakili negara
      $tag_ranked = "classification_country_id";
      $select_classification_query = "countries.name as classification_name";
    }

    if ($parent_classfification_id == 3) { // jika mewakili provinsi
      $tag_ranked = "classification_province_id";
      if ($event->classification_country_id == 102) {
        $select_classification_query = "provinces.name as classification_name";
      } else {
        $select_classification_query = "states.name as classification_name";
      }
    }

    if ($parent_classfification_id == 4) { // jika mewakili kota
      $tag_ranked = "city_id";
      if ($event->classification_country_id == 102) {
        $select_classification_query = "cities.name as classification_name";
      } else {
        $select_classification_query = "cities_of_countries.name as classification_name";
      }
    }

    if ($parent_classfification_id == 6) { // jika berasal dari settingan admin
      $tag_ranked = "children_classification_id";
      $select_classification_query = "children_classification_members.title as classification_name";
    }

    $participants = ArcheryEventParticipant::where("event_category_id", $category_detail_team->id)->where("status", 1);

    if ($parent_classfification_id == 1) { // jika mewakili club
      $participants = $participants->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
    }

    if ($parent_classfification_id == 2) { // jika mewakili negara
      $participants = $participants->join("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
    }

    if ($parent_classfification_id == 3) { // jika mewakili provinsi
      if ($event->classification_country_id == 102) {
        $participants = $participants->join("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
      } else {
        $participants = $participants->join("states", "states.id", "=", "archery_event_participants.classification_province_id");
      }
    }

    if ($parent_classfification_id == 4) { // jika mewakili kota
      if ($event->classification_country_id == 102) {
        $participants = $participants->join("cities", "cities.id", "=", "archery_event_participants.city_id");
      } else {
        $participants = $participants->join("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
      }
    }

    if ($parent_classfification_id == 6) { // jika berasal dari settingan admin
      $participants = $participants->join("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");
    }

    $participants = $participants->select(
      "archery_event_participants.*",
      $select_classification_query
    )->get();

    foreach ($participants as $key => $value) {
      $list_data_members = [];
      $total_per_point = self::$total_per_points;
      $total = 0;
      $sequence[$value[$tag_ranked]] = isset($sequence[$value[$tag_ranked]]) ? $sequence[$value[$tag_ranked]] + 1 : 1;
      foreach ($qualification_male as $k => $male_rank) {
        if ($value[$tag_ranked] != $male_rank[$tag_ranked]) {
          continue;
        }

        if ($is_live_score != 1) {
          if ($male_rank["total"]  < 1 && $male_rank["total_arrow"] == 0) {
            continue;
          }
        }

        $is_insert = 0;
        if ($value->is_special_team_member == 1) {
          $tem_member_special = TeamMemberSpecial::where("participant_team_id", $value->id)
            ->where("participant_individual_id", $male_rank["member"]["participant_id"])
            ->first();

          if ($tem_member_special) {
            $is_insert = 1;
          }
        } else {
          $check_is_exists = TeamMemberSpecial::join("archery_event_participants", "archery_event_participants.id", "=", "team_member_special.participant_team_id")
            ->where("team_member_special.participant_individual_id", $male_rank["member"]["participant_id"])
            ->where("archery_event_participants.event_category_id", $value->event_category_id)
            ->first();

          if ($check_is_exists) {
            $is_insert = 0;
          } else {
            $is_insert = 1;
          }
        }

        if ($is_insert == 1) {
          foreach ($male_rank["total_per_points"] as $p => $t) {
            $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
          }
          $male_rank["member"]["total"] = $male_rank["total"];
          $total = $total + $male_rank["total"];
          $list_data_members[] = $male_rank["member"];
          unset($qualification_male[$k]);
        } else {
          continue;
        }

        if (count($list_data_members) == 1) {
          break;
        }
      }

      foreach ($qualification_female as $ky => $female_rank) {
        if ($value[$tag_ranked] != $female_rank[$tag_ranked]) {
          continue;
        }

        if ($is_live_score != 1) {
          if ($female_rank["total"]  < 1 && $female_rank["total_arrow"] == 0) {
            continue;
          }
        }


        $is_insert = 0;
        if ($value->is_special_team_member == 1) {
          $tem_member_special = TeamMemberSpecial::where("participant_team_id", $value->id)
            ->where("participant_individual_id", $female_rank["member"]["participant_id"])
            ->first();
          if ($tem_member_special) {
            $is_insert = 1;
          }
        } else {
          $check_is_exists = TeamMemberSpecial::join("archery_event_participants", "archery_event_participants.id", "=", "team_member_special.participant_team_id")
            ->where("team_member_special.participant_individual_id", $female_rank["member"]["participant_id"])
            ->where("archery_event_participants.event_category_id", $value->event_category_id)
            ->first();

          if ($check_is_exists) {
            $is_insert = 0;
          } else {
            $is_insert = 1;
          }
        }

        if ($is_insert == 1) {
          foreach ($female_rank["total_per_points"] as $p => $t) {
            $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
          }
          $female_rank["member"]["total"] = $female_rank["total"];
          $total = $total + $female_rank["total"];
          $list_data_members[] = $female_rank["member"];
          unset($qualification_female[$ky]);
        } else {
          continue;
        }

        if (count($list_data_members) == 2) {
          break;
        }
      }

      $team = $value["classification_name"] . " " . $sequence[$value[$tag_ranked]];

      $participant_club_or_city[] = [
        "participant_id" => $value->id,
        "is_special_team_member" => $value->is_special_team_member,
        "classification_name" => $value->classification_name,
        "team" => $team,
        "total" => $total,
        "total_x_plus_ten" => isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
        "total_x" => isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
        "total_per_points" => $total_per_point,
        "total_tmp" => ArcheryScoring::getTotalTmp($total_per_point, $total),
        "teams" => $list_data_members
      ];
    }
    usort($participant_club_or_city, function ($a, $b) {
      return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
    });

    $new_array = [];
    foreach ($participant_club_or_city as $key => $value) {
      if (count($value["teams"]) == 2) {
        array_push($new_array, $value);
      }
    }
    return $new_array;
  }

  public static function teamBestOfThree(ArcheryEventCategoryDetail $category_detail_team, int $is_live_score = 0)
  {
    $event = ArcheryEvent::find($category_detail_team->event_id);
    if (!$event) {
      throw new BLoCException("event not found");
    }

    $team_cat = ($category_detail_team->team_category_id) == "male_team" ? "individu male" : "individu female";
    $category_detail_individu = ArcheryEventCategoryDetail::where("event_id", $category_detail_team->event_id)
      ->where("age_category_id", $category_detail_team->age_category_id)
      ->where("competition_category_id", $category_detail_team->competition_category_id)
      ->where("distance_id", $category_detail_team->distance_id)
      ->where("team_category_id", $team_cat)
      ->first();

    if (!$category_detail_individu) {
      throw new BLoCException("categori individu not found");
    }

    $session = $category_detail_individu->getArraySessionCategory();
    $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_detail_individu->id, 1, $session, false, null, false, 1);

    $participant_club_or_city = [];
    $sequence = [];

    $parent_classfification_id = $event->parent_classification;

    if ($parent_classfification_id == 0) {
      throw new BLoCException("parent calassification_id invalid");
    }

    $tag_ranked = "club_id";
    $select_classification_query = "archery_clubs.name as classification_name";

    if ($parent_classfification_id == 2) { // jika mewakili negara
      $tag_ranked = "classification_country_id";
      $select_classification_query = "countries.name as classification_name";
    }

    if ($parent_classfification_id == 3) { // jika mewakili provinsi
      $tag_ranked = "classification_province_id";
      if ($event->classification_country_id == 102) {
        $select_classification_query = "provinces.name as classification_name";
      } else {
        $select_classification_query = "states.name as classification_name";
      }
    }

    if ($parent_classfification_id == 4) { // jika mewakili kota
      $tag_ranked = "city_id";
      if ($event->classification_country_id == 102) {
        $select_classification_query = "cities.name as classification_name";
      } else {
        $select_classification_query = "cities_of_countries.name as classification_name";
      }
    }

    if ($parent_classfification_id == 6) { // jika berasal dari settingan admin
      $tag_ranked = "children_classification_id";
      $select_classification_query = "children_classification_members.title as classification_name";
    }

    $participants = ArcheryEventParticipant::where("event_category_id", $category_detail_team->id)
      ->where("status", 1);

    if ($parent_classfification_id == 1) { // jika mewakili club
      $participants = $participants->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
    }

    if ($parent_classfification_id == 2) { // jika mewakili negara
      $participants = $participants->join("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
    }

    if ($parent_classfification_id == 3) { // jika mewakili provinsi
      if ($event->classification_country_id == 102) {
        $participants = $participants->join("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
      } else {
        $participants = $participants->join("states", "states.id", "=", "archery_event_participants.classification_province_id");
      }
    }

    if ($parent_classfification_id == 4) { // jika mewakili kota
      if ($event->classification_country_id == 102) {
        $participants = $participants->join("cities", "cities.id", "=", "archery_event_participants.city_id");
      } else {
        $participants = $participants->join("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
      }
    }

    if ($parent_classfification_id == 6) { // jika berasal dari settingan admin
      $participants = $participants->join("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");
    }

    $participants = $participants->select(
      "archery_event_participants.*",
      $select_classification_query
    )->get();

    foreach ($participants as $key => $value) {
      $list_data_members = [];
      $total_per_point = self::$total_per_points;
      $total = 0;

      $sequence[$value[$tag_ranked]] = isset($sequence[$value[$tag_ranked]]) ? $sequence[$value[$tag_ranked]] + 1 : 1;

      foreach ($qualification_rank as $k => $member_rank) {
        if ($value[$tag_ranked] != $member_rank[$tag_ranked]) {
          continue;
        }

        if ($is_live_score != 1) {
          if ($member_rank["total"]  < 1 && $member_rank["total_arrow"] == 0) {
            continue;
          }
        }

        $is_insert = 0;
        if ($value->is_special_team_member == 1) {
          $tem_member_special = TeamMemberSpecial::where("participant_team_id", $value->id)
            ->where("participant_individual_id", $member_rank["member"]["participant_id"])
            ->first();
          if ($tem_member_special) {
            $is_insert = 1;
          }
        } else {
          $check_is_exists = TeamMemberSpecial::join("archery_event_participants", "archery_event_participants.id", "=", "team_member_special.participant_team_id")
            ->where("team_member_special.participant_individual_id", $member_rank["member"]["participant_id"])
            ->where("archery_event_participants.event_category_id", $value->event_category_id)
            ->first();

          if ($check_is_exists) {
            $is_insert = 0;
          } else {
            $is_insert = 1;
          }
        }

        if ($is_insert == 1) {
          foreach ($member_rank["total_per_points"] as $p => $t) {
            $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
          }
          $member_rank["member"]["total"] = $member_rank["total"];
          $total = $total + $member_rank["total"];
          $list_data_members[] = $member_rank["member"];
          unset($qualification_rank[$k]);
        } else {
          continue;
        }


        if (count($list_data_members) == 3) {
          break;
        }
      }

      $team = $value["classification_name"] . " " . $sequence[$value[$tag_ranked]];

      $participant_club_or_city[] = [
        "participant_id" => $value->id,
        "classification_name" => $value->classification_name,
        "is_special_team_member" => $value->is_special_team_member,
        "team" => $team,
        "total" => $total,
        "total_x_plus_ten" => isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
        "total_x" => isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
        "total_per_points" => $total_per_point,
        "total_tmp" => ArcheryScoring::getTotalTmp($total_per_point, $total),
        "teams" => $list_data_members
      ];
    }

    usort($participant_club_or_city, function ($a, $b) {
      return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
    });

    $new_array = [];
    foreach ($participant_club_or_city as $key => $value) {
      if (count($value["teams"]) == 3) {
        array_push($new_array, $value);
      }
    }
    return $new_array;
  }

  // digunakan untuk mendapatkan data qualification atau elimination dari peringkat satu sampai 3
  public static function getData($category_detail_id, $type, $event_id)
  {
    $data_report = [];
    $category_id = null;
    $elimination_rank = 0;

    $members = ArcheryEventEliminationMember::select(
      "*",
      "archery_event_category_details.id as category_details_id",
      "archery_event_participant_members.id as participant_member_id",
      "cities.name as city_name",
      "users.name as member_name",
      "archery_clubs.name as club_name",
      DB::RAW('date(archery_event_elimination_members.created_at) as date')
    )
      ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
      ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->join("users", "users.id", "=", "archery_event_participants.user_id")
      ->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id")
      ->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
      ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
      ->where("archery_event_category_details.id", $category_detail_id)
      ->where("archery_event_participants.event_id", $event_id)
      ->where(function ($query) use ($type) {
        if ($type == "elimination") {
          $query->where("archery_event_elimination_members.elimination_ranked", '>', 0);
          $query->where("archery_event_elimination_members.elimination_ranked", '<=', 3);
          $query->orderBy('archery_event_elimination_members.elimination_ranked', 'ASC');
        } else if ($type == "qualification") {
          $query->where("archery_event_elimination_members.position_qualification", '>', 0);
          $query->where("archery_event_elimination_members.position_qualification", '<=', 3);
          $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
        } else {
          $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
        }
      })
      ->orderBy('archery_event_participants.event_category_id', 'ASC')
      ->orderBy('archery_event_category_details.team_category_id', 'DESC')
      ->get();


    if ($members) {
      foreach ($members as $member) {

        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);

        if ($type == "elimination") {
          $elimination_rank = $member->elimination_ranked;
          if ($member->elimination_ranked == 1) {
            $medal = 'Gold';
          } else if ($member->elimination_ranked == 2) {
            $medal = 'Silver';
          } else {
            $medal = 'Bronze';
          }
        } elseif ($type == "qualification") {
          if ($member->position_qualification == 1) {
            $medal = 'Gold';
          } else if ($member->position_qualification == 2) {
            $medal = 'Silver';
          } else {
            $medal = 'Bronze';
          }
        } else {
          $medal = '-';
        }

        $athlete = $member->member_name;
        $date = $member->date;

        $club = $member->club_name;
        $city = $member->city_name;

        $category = ArcheryEventCategoryDetail::find($member->category_details_id);
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
          $session[] = $i + 1;
        }
        $scoring = ArcheryScoring::generateScoreBySession($member->participant_member_id, 1, $session);

        $data_report[] = array(
          "athlete" => $athlete,
          "club" => $club,
          "city" => $city,
          "category" => $categoryLabel,
          "medal" => $medal,
          "date" => $date,
          "scoring" => $scoring,
          "elimination_rank" => $elimination_rank,
          "participant_id" => $member->archery_event_participant_id,
        );

        $category_id = $member->category_details_id;
      }
    }

    if ($type == "elimination") {
      $sorted_data = collect($data_report)->sortBy('elimination_rank')->values()->all();
      return array($sorted_data, $category_id);
    }

    $sorted_data = collect($data_report)->sortByDesc('scoring.total_tmp')->values()->all();

    return array($sorted_data, $category_id);
  }

  public static function getTemplateIndividu($category)
  {
    $elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
    $elimination_id = 0;
    $elimination_member_count = 16;

    if ($elimination) {
      $elimination_id = $elimination->id;
      $elimination_member_count = $elimination->count_participant;
    } elseif ($category->default_elimination_count != 0) {
      $elimination_member_count = $category->default_elimination_count;
    }


    $score_type = 1; // 1 for type qualification
    $session = [];
    for ($i = 0; $i < $category->session_in_qualification; $i++) {
      $session[] = $i + 1;
    }

    $fix_members1 = ArcheryEventEliminationMatch::select(
      "archery_event_elimination_members.position_qualification",
      "users.name",
      "archery_event_participant_members.id AS member_id",
      "archery_event_participant_members.archery_event_participant_id as participant_id",
      "archery_event_participant_members.gender",
      "archery_event_elimination_matches.id",
      "archery_event_elimination_matches.round",
      "archery_event_elimination_matches.match",
      "archery_event_elimination_matches.win",
      "archery_event_elimination_matches.bud_rest",
      "archery_event_elimination_matches.target_face",
      "archery_event_elimination_matches.result"
    )
      ->leftJoin("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
      ->leftJoin("archery_event_participant_members", "archery_event_elimination_members.member_id", "=", "archery_event_participant_members.id")
      ->leftJoin("users", "users.id", "=", "archery_event_participant_members.user_id")
      ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
      ->orderBy("archery_event_elimination_matches.round")
      ->orderBy("archery_event_elimination_matches.match")
      ->orderBy("archery_event_elimination_matches.index")
      ->get();

    // return $fix_members1;

    $qualification_rank = [];
    $updated = true;
    if ($fix_members1->count() > 0) {
      $members = [];
      foreach ($fix_members1 as $key => $value) {
        $members[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
        if ($value->member_id != null) {
          $archery_scooring = ArcheryScoring::where("item_id", $value->id)->where("item_value", "archery_event_elimination_matches")->first();
          $admin_total = "";
          $is_different = 0;
          $total_scoring = 0;

          if ($archery_scooring) {
            $admin_total = $archery_scooring->admin_total;
            $scoring_detail = json_decode($archery_scooring->scoring_detail);

            if ($admin_total != 0) {
              $total_scoring = $admin_total;
            } else {
              $total_scoring = isset($scoring_detail->result) ? $scoring_detail->result : $scoring_detail->total;
            }

            if ($total_scoring != $admin_total) {
              $is_different = 1;
            }
          }

          $club =  ArcheryEventParticipant::select("archery_clubs.name")->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")->where("archery_event_participants.id", $value->participant_id)->where("archery_event_participants.status", 1)->first();

          $members[$value->round][$value->match]["teams"][] = array(
            "id" => $value->member_id,
            "match_id" => $value->id,
            "name" => $value->name,
            "gender" => $value->gender,
            "club" =>  $club->name ?? '-',
            "potition" => $value->position_qualification,
            "win" => $value->win,
            "total_scoring" => $total_scoring,
            "status" => $value->win == 1 ? "win" : "wait",
            "admin_total" => $admin_total,
            "result" => $value->result,
            "budrest_number" => $value->bud_rest != 0 ? $value->bud_rest . "" . $value->target_face : "",
            "is_different" => $is_different,
          );
        } else {
          $match =  ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)->where("round", $value->round)->where("match", $value->match)->get();
          if ($match[0]->elimination_member_id == 0 && $match[1]->win == 1) {
            $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
          } elseif ($match[1]->elimination_member_id == 0 && $match[0]->win == 1) {
            $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
          } elseif (($match[1]->elimination_member_id == 0 && $match[0]->elimination_member_id == 0) && $value->round == 1) {
            $members[$value->round][$value->match]["teams"][] = ["status" => "wait"];
          } else {
            $members[$value->round][$value->match]["teams"][] = ["status" => "wait"];
          }
        }
      }

      $fix_members2 = $members;
      $updated = false;
      $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_members2, $elimination_member_count);
    } else {
      $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category->id, $score_type, $session, false, null, true, 1);
      $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);
    }
    $template["updated"] = $updated;
    $template["elimination_id"] = $elimination_id;
    return $template;
  }

  public static function getTemplateTeam($category_team)
  {
    $elimination = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
    $elimination_id = 0;
    $elimination_member_count = 16;
    if ($elimination) {
      $elimination_id = $elimination->id;
      $elimination_member_count = $elimination->count_participant;
    } elseif ($category_team->default_elimination_count != 0) {
      $elimination_member_count = $category_team->default_elimination_count;
    }

    $session = [];
    for ($i = 0; $i < $category_team->session_in_qualification; $i++) {
      $session[] = $i + 1;
    }

    $fix_teams_1 = ArcheryEventEliminationGroupMatch::select(
      "archery_event_elimination_group_teams.position",
      "archery_event_elimination_group_teams.participant_id",
      "archery_event_elimination_group_teams.team_name",
      "archery_event_elimination_group_match.id",
      "archery_event_elimination_group_match.round",
      "archery_event_elimination_group_match.match",
      "archery_event_elimination_group_match.win",
      "archery_event_elimination_group_match.bud_rest",
      "archery_event_elimination_group_match.target_face",
      "archery_event_elimination_group_match.elimination_group_id"
    )
      ->leftJoin("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
      ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
      ->orderBy("archery_event_elimination_group_match.round")
      ->orderBy("archery_event_elimination_group_match.match")
      ->orderBy("archery_event_elimination_group_match.index")
      ->get();

    $lis_team = [];

    $updated = true;
    if ($fix_teams_1->count() > 0) {
      $teams = [];
      foreach ($fix_teams_1 as $key => $value) {
        $teams[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
        if ($value->participant_id != null) {
          $archery_scooring_team = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $value->id)->first();
          $admin_total = "";
          $is_different = 0;
          $total_scoring = 0;
          if ($archery_scooring_team) {
            $admin_total = $archery_scooring_team->admin_total;
            $scoring_detail = json_decode($archery_scooring_team->scoring_detail);

            if ($admin_total != 0) {
              $total_scoring = $admin_total;
            } else {
              $total_scoring = $scoring_detail->result;
            }

            if ($total_scoring != $admin_total) {
              $is_different = 1;
            }
          }
          $list_member = [];
          $list_group_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $value->participant_id)->get();
          if ($list_group_team->count() > 0) {
            foreach ($list_group_team as $gt) {
              $m = ArcheryEventParticipantMember::select("archery_event_participant_members.user_id as user_id", "archery_event_participant_members.id as member_id", "users.name")
                ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
                ->where("archery_event_participant_members.id", $gt->member_id)
                ->first();

              $list_member[] = $m;
            }
          }

          $team_name = $value->team_name;

          $teams[$value->round][$value->match]["teams"][] = array(
            "participant_id" => $value->participant_id,
            "match_id" => $value->id,
            "potition" => $value->position,
            "win" => $value->win,
            "result" => $total_scoring,
            "status" => $value->win == 1 ? "win" : "wait",
            "admin_total" => $admin_total,
            "budrest_number" => $value->bud_rest != 0 ? $value->bud_rest . "" . $value->target_face : "",
            "is_different" => $is_different,
            "member_team" => $list_member,
            "team_name" => $team_name
          );
        } else {
          $match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)->where("round", $value->round)->where("match", $value->match)->get();
          if ($match[0]->group_team_id == 0 && $match[1]->win == 1) {
            $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
          } elseif ($match[1]->group_team_id == 0 && $match[0]->win == 1) {
            $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
          } elseif (($match[0]->group_team_id == 0 && $match[1]->group_team_id == 0) && $value->round == 1) {
            $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
          } else {
            $teams[$value->round][$value->match]["teams"][] = ["status" => "wait"];
          }
        }
      }

      $fix_team_2 = $teams;
      $updated = false;
      $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_team_2, $elimination_member_count);
    } else {
      if ($category_team->team_category_id == "mix_team") {
        $lis_team = self::mixTeamBestOfThree($category_team);
      } else {
        $lis_team = self::teamBestOfThree($category_team);
      }
      $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
    }
    $template["updated"] = $updated;
    $template["elimination_group_id"] = $elimination_id;
    return $template;
  }

  public static function getDataEliminationTeam($category_detail_id)
  {
    $elimination_group = ArcheryEventEliminationGroup::where('category_id', $category_detail_id)->first();
    if ($elimination_group) {
      $elimination_group_match = ArcheryEventEliminationGroupMatch::select(DB::RAW('distinct group_team_id as teamid'))
        ->where('elimination_group_id', $elimination_group->id)
        ->get();

      $data = array();
      foreach ($elimination_group_match as $key => $value) {

        $elimination_group_team = ArcheryEventEliminationGroupTeams::where('id', $value->teamid)->first();

        if ($elimination_group_team) {
          if ($elimination_group_team->elimination_ranked <= 3) {
            $participant = ArcheryEventParticipant::select("archery_clubs.name as club_name", "cities.name as city_name")
              ->where("archery_event_participants.id", $elimination_group_team->participant_id)
              ->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
              ->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id")
              ->first();
            $club_name = "";
            $city_name = "";
            if ($participant) {
              $club_name = $participant->club_name;
              $city_name = $participant->city_name;
            }

            $data[] = [
              'id' => $elimination_group_team->id,
              'participant_id' => $elimination_group_team->participant_id,
              'club_name' => $club_name,
              'city_name' => $city_name,
              'team_name' => $elimination_group_team->team_name,
              'elimination_ranked' => $elimination_group_team->elimination_ranked ?? 0,
              'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail_id),
              'date' => $elimination_group->created_at->format('Y-m-d'),
              "member_team" => ArcheryEventEliminationGroupMemberTeam::select("users.name", "archery_event_participant_members.id as member_id")->where("participant_id", $elimination_group_team->participant_id)
                ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_elimination_group_member_team.member_id")
                ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
                ->get()
            ];
          } else {
            continue;
          }
        }
      }

      $sorted_data = collect($data)->sortBy('elimination_ranked')->values()->take(3);
      return $sorted_data;
    }
  }
}
