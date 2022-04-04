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

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $category_bud_rest = $parameters->get('category_bud_rest', []);

        if (count($category_bud_rest) > 0) {
            foreach ($category_bud_rest as $cb) {
                $category = ArcheryEventCategoryDetail::find($cb["category_id"]);

                if (!$category) {
                    throw new BLoCException('category tidak ditemukan');
                }

                if ($category->type != 'Individual') {
                    throw new BLoCException("tipe category detail id harus tipe individual");
                }

                if ($cb['bud_rest_start'] > $cb['bud_rest_end'] || $cb['bud_rest_end'] < $cb['bud_rest_start']) {
                    throw new BLoCException("bud rest start tidak boleh lebih besar dari bud rest end, begitu pula sebaliknya");
                }

                if ($cb['target_face'] > 4) { //4 adalah max ukuran bantalan
                    throw new BLoCException("target face tidak boleh lebih dari 4");
                }

                $budrest = Budrest::where('archery_event_category_id', $cb['category_id'])->first();

                if (!$budrest) {
                    $budrest = new Budrest();
                    $budrest->archery_event_category_id = $cb['category_id'];
                    $budrest->bud_rest_start =  $cb['bud_rest_start'];
                    $budrest->bud_rest_end =  $cb['bud_rest_end'];
                    $budrest->target_face =  $cb['target_face'];
                    $budrest->type =  $cb['type'];
                    $budrest->save();
                } else {
                    $check_qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $cb['category_id'])->first();

                    if (is_null($check_qualification_time->event_start_datetime)) {
                        throw new BLoCException("set jadwal kualifikasi terlebih dahulu ");
                    }

                    if (strtotime($check_qualification_time->event_start_datetime) < strtotime('now')) {
                        throw new BLoCException("tidak bisa update data karna sudah lewat dari tanggal mulai qualifikasi");
                    }

                    $budrest->archery_event_category_id = $cb['category_id'];
                    $budrest->bud_rest_start =  $cb['bud_rest_start'];
                    $budrest->bud_rest_end =  $cb['bud_rest_end'];
                    $budrest->target_face =  $cb['target_face'];
                    $budrest->type =  $cb['type'];
                    $budrest->save();
                }
            }

            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $cb['category_id'], $cb['category_id']);
        }

        return $budrest;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer',
            'category_bud_rest' => 'required|array',
            'category_bud_rest.*.category_id' => 'required|integer',
            'category_bud_rest.*.bud_rest_start' => 'required|integer|min:1',
            'category_bud_rest.*.bud_rest_end' => 'required|integer|min:1',
            'category_bud_rest.*.target_face' => 'required|integer|min:1',
            'category_bud_rest.*.type' => 'required|in:qualification,elimination'
        ];
    }
}
