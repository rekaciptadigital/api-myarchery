<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventRegistrationFee extends Model
{
    public function archeryEventRegistrationFeePerCategory()
    {
        return $this->hasMany(ArcheryEventRegistrationFeePerCategory::class, 'event_registration_fee_id', 'id');
    }
}
