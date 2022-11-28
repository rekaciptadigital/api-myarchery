<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Database\Eloquent\Collection;

class MemberScoringExport implements FromCollection
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection($this->array);
    }
}
