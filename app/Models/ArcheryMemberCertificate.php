<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryMemberCertificate extends Model
{
    protected $fillable = [
        'id',
        'member_id',
        'certificate_template_id',
       ];
}
