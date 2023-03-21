<?php

namespace App\BLoC\General;

use App\Models\ArcheryEventParticipant;
use App\Models\ClassificationEventRegisters;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class InsertDataParticipantToClassificationEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $data = [];
        $tableParticipants = new ArcheryEventParticipant();
        $tableClassificationEventRegister = new ClassificationEventRegisters();

        $get_all_participants = $tableParticipants
            ->get();

        foreach ($get_all_participants as $key => $value) {
            // array_push($data, [
            //     'user_id' => $value['user_id'],
            //     'event_id' => $value['event_id'],
            //     'city_id' => $value['city_id'],
            //     'provinsi_id' => $value['classification_province_id'],
            //     'country_id' => $value['classification_country_id'],
            //     'archery_club_id' => $value['club_id'],
            //     'children_classification' => $value['children_classification_id'],
            // ]);
            $tableClassificationEventRegister->insert([
                'user_id' => $value['user_id'],
                'event_id' => $value['event_id'],
                'city_id' => $value['city_id'],
                'provinsi_id' => $value['classification_province_id'],
                'country_id' => $value['classification_country_id'],
                'archery_club_id' => $value['club_id'],
                'children_classification' => $value['children_classification_id'],
            ]);
        }

        // return $data;


        return 'success';
    }

    protected function validation($parameters)
    {
        return [];
    }
}
