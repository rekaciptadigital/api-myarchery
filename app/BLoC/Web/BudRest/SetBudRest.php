<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

use DateTimeZone;

class SetBudRest extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get('event_id');

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException('event tidak ditemukan');
        }

        $carbon_event_end_datetime = Carbon::parse($event->event_end_datetime);
        $new_format_event_end_datetime = Carbon::create($carbon_event_end_datetime->year, $carbon_event_end_datetime->month, $carbon_event_end_datetime->day, 0, 0, 0);

        if ($new_format_event_end_datetime < Carbon::today()) {
            throw new BLoCException('event telah selesai');
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $datas = $parameters->get('event_category', []);

        foreach ($datas as $data) {
            $check_category = ArcheryEventCategoryDetail::select('archery_master_team_categories.type')
                ->leftJoin('archery_master_team_categories', 'archery_master_team_categories.id', 'archery_event_category_details.team_category_id')
                ->where('archery_event_category_details.id', $data['archery_event_category_id'])->where('event_id', $event_id)->first();

            if (!$check_category) {
                throw new BLoCException('category detail id tidak ditemukan');
            }

            if ($check_category->type != 'Individual') {
                throw new BLoCException("tipe category detail id harus tipe individual");
            }

            if ($data['bud_rest_start'] > $data['bud_rest_end'] || $data['bud_rest_end'] < $data['bud_rest_start']) {
                throw new BLoCException("bud rest start tidak boleh lebih besar dari bud rest end, begitu pula sebaliknya");
            }

            if ($data['target_face'] > 4) { //4 adalah max ukuran bantalan
                throw new BLoCException("target face tidak boleh lebih dari 4");
            }

            $budrest = Budrest::where('archery_event_category_id', $data['archery_event_category_id'])->first();

            if (!$budrest) {
                $budrest = new Budrest();
                $budrest->archery_event_category_id = $data['archery_event_category_id'];
                $budrest->bud_rest_start =  $data['bud_rest_start'];
                $budrest->bud_rest_end =  $data['bud_rest_end'];
                $budrest->target_face =  $data['target_face'];
                $budrest->type =  $data['type'];
                $budrest->save();
            } else {
                $check_qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $data['archery_event_category_id'])->first();

                $now = Carbon::now();

                if (is_null($check_qualification_time->event_start_datetime)) {
                    throw new BLoCException("set jadwal kualifikasi terlebih dahulu ");
                }

                // if (strtotime($check_qualification_time->event_start_datetime) < strtotime('now')){
                //     throw new BLoCException("tidak bisa update data karna sudah lewat dari tanggal mulai qualifikasi");

                // }

                $budrest->archery_event_category_id = $data['archery_event_category_id'];
                $budrest->bud_rest_start =  $data['bud_rest_start'];
                $budrest->bud_rest_end =  $data['bud_rest_end'];
                $budrest->target_face =  $data['target_face'];
                $budrest->type =  $data['type'];
                $budrest->save();
            }
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $data['archery_event_category_id'], $data['archery_event_category_id']);
        }

        return $budrest;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer',
            'event_category' => 'required|array',
            'event_category.*.archery_event_category_id' => 'required|integer',
            'event_category.*.bud_rest_start' => 'required|integer|min:1',
            'event_category.*.bud_rest_end' => 'required|integer|min:1',
            'event_category.*.target_face' => 'required|integer|min:1',
            'event_category.*.type' => 'required|in:qualification,elimination'
        ];
    }
}
