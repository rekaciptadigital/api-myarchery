<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventParticipantMemberNumber extends Model
{
    protected $table = 'archery_event_participant_member_numbers';
    protected $fillable = ['prefix', 'participant_member_id'];
}