<?php

namespace App\Exports;

use App\Exports\Sheets\ListCategoryIdSheet;
use App\Exports\Sheets\ListClubId;
use App\Exports\Sheets\TemplateUploadMemberCollectiveClub;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MemberExportClub implements WithMultipleSheets
{
    use Exportable;

    protected $event_id;

    public function __construct(int $event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new TemplateUploadMemberCollectiveClub();
        $sheets[] = new ListClubId();
        $sheets[] = new ListCategoryIdSheet($this->event_id);

        return $sheets;
    }
}
