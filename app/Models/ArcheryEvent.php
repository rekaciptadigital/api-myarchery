<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ArcheryEvent extends Model
{
    protected $appends = ['event_url'];

    public function archeryEventCategories()
    {
        return $this->hasMany(ArcheryEventCategory::class, 'event_id', 'id');
    }

    public function archeryEventQualifications()
    {
        return $this->hasMany(ArcheryEventQualification::class, 'event_id', 'id');
    }

    public function archeryEventRegistrationFees()
    {
        return $this->hasMany(ArcheryEventRegistrationFee::class, 'event_id', 'id');
    }

    public function archeryEventTargets()
    {
        return $this->hasMany(ArcheryEventTarget::class, 'event_id', 'id');
    }

    public function getQualificationSessionLengthAttribute($value)
    {
        return json_decode($value);
    }

    public function getPosterAttribute($value)
    {
        return $value ? route('api_display', ['file_path' => $value]) : $value;
    }

    public function getHandbookAttribute($value)
    {

        return $value ? route('api_download', ['file_path' => $value]) : $value;
    }

    public function getIsFlatRegistrationFeeAttribute($value)
    {
        return $value == 1 || $value == '1';
    }

    public function getQualificationWeekdaysOnlyAttribute($value)
    {
        return $value == 1 || $value == '1';
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function getEventUrlAttribute()
    {
        return env('WEB_DOMAIN', 'https://my-archery.id') . '/' . Str::slug($this->admin->name) . '/' . $this->event_slug;
    }
}
