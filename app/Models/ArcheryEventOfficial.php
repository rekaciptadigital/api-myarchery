<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class ArcheryEventOfficial extends Model
{
    public static $relation_with_participant = [
        '1' => 'Pelatih',
        '2' => 'Manager Club/Tim',
        '3' => 'Orang Tua',
        '4' => 'Saudara',
        '0' => 'Lainnya'
    ];
    
    protected $table = 'archery_event_official';
    protected $guarded = 'id'; 
}