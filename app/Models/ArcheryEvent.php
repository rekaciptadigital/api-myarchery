<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEvent extends Model
{

    public function archeryEventCategories()
    {
        return $this->hasMany(ArcheryEventCategory::class);
    }

    public function getQualificationSessionLengthAttribute($value)
    {
        return json_decode($value);
    }

    public function getPosterAttribute($value)
    {
        return route('api_display', ['file_path' => $value]);
    }

    public function getHandbookAttribute($value)
    {
        return route('api_display', ['file_path' => $value]);
    }

    public function getIsFlatRegistrationFeeAttribute($value) {
        return $value == 1 || $value == '1';
    }

    public function getQualificationWeekdaysOnlyAttribute($value) {
        return $value == 1 || $value == '1';
    }
}
