<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantMemberTeam extends Model
{
    protected $table = 'participant_member_teams';
    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
