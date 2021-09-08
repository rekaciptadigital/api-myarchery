<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventQualification extends Model
{
    public function archeryEventQualificationDetails()
    {
        return $this->hasMany(ArcheryEventQualificationDetail::class, 'event_qualification_id', 'id');
    }
}
