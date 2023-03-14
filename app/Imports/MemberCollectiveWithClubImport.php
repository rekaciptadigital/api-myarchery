<?php

namespace App\Imports;

use App\Imports\Sheets\MemberCollectiveWithClubImportSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MemberCollectiveWithClubImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Template' => new MemberCollectiveWithClubImportSheet(),
        ];
    }
}
