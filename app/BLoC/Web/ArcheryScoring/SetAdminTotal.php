<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;

class FindParticipantScoreBySchedule extends Retrieval
{
    public function getDescription()
    {
        return "memberi nilan admin_total dari halaman get list skoring eliminasi";
    }

    protected function process($parameters)
    {
        // 1. tangkap param code
        // 2. pecah string code jadi array
        // 3. tangkap index 0 dan simpan sebagai variabel type
        // 4. pastikan type = 2
        // 5. tangkap index 1 dan simpan ke variabel elimination id
        // 6. pastikan elimination id tsb terdapat di db
        // 7. tangkap index 2 dan simpan ke variabel match
        // 8. tangkap index 3 dan simpan ke variabel round
        // 9. cari di tabel elimination match yang elimination, match dan round sesuai
        // 10. pastikan ketersediaan member
        // 11. 
    }

    protected function validation($parameters)
    {
        return [
            "code" => "required"
        ];
    }
}
