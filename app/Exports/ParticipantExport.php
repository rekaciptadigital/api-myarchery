<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ParticipantExport implements FromCollection
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }
    public function collection()
    {
        return new Collection($this->array);
    }
}
