<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetBudRestElimination extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $elimination_id = $parameters->get("elimination_id");
        $match = $parameters->get("match");
        $round = $parameters->get("round");
        $budrest_number = $parameters->get("budrest_number");

        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination data tidak ditemukan");
        }

        $match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
            ->where("match", $match)
            ->where("round", $round)
            ->get();

        if ($match->count() != 2) {
            throw new BLoCException("match invalid");
        }

        // split budrest number dan target face
        $brn = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $budrest_number);
        $bud_rest = $brn[0];
        $target_face = $brn[1];

        foreach ($match as $key => $value) {
            $value->bud_rest = $bud_rest;
            $value->target_face = $target_face;
            $value->save();
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "elimination_id" => "required|integer",
            "round" => "required|integer",
            "match" => "required|integer",
            "budrest_number" => "required|string"
        ];
    }
}
