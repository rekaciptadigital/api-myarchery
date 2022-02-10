<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\User;

use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryUserAthleteCode;

class RingkasanPesertaSheet implements FromView, WithColumnWidths, WithHeadings
{
    protected $event_id;

    function __construct($event_id) {
            $this->event_id = $event_id;
    }

    public function view(): View
    {
        $event_id=$this->event_id ;
        $admin = Auth::user();

        $jenis_regu= DB::select('SELECT archery_events.event_name, date(event_start_datetime) as event_start_datetime,date(event_end_datetime)as event_end_datetime, archery_event_category_details.fee,archery_event_category_details.team_category_id,  sum(quota) as total,ifnull(t_jual.total_terjual,0) as total_terjual, (sum(quota))-ifnull(t_jual.total_terjual,0) as sisa_kuota, ifnull(fee *t_jual.total_terjual,0)as total_uang_masuk  FROM archery_event_category_details left join (select team_category_id,count(archery_event_participants.team_category_id) as total_terjual from archery_event_participants where event_id=? group by team_category_id)t_jual on t_jual.team_category_id= archery_event_category_details.team_category_id left join archery_events on archery_events.id = archery_event_category_details.event_id where archery_event_category_details.event_id=? group by archery_event_category_details.team_category_id,t_jual.total_terjual, fee,event_name, event_start_datetime,event_end_datetime',[$event_id,$event_id]);
        $nama_regu= DB::select('SELECT archery_master_team_categories.team_name,ifnull(max(fee),0) as harga,ifnull(sum(quota),0) as total_kuota,ifnull(t_pendaftar.total_pendaftar,0) as total_pendaftar ,sum(quota)-ifnull(t_pendaftar.total_pendaftar,0) as sisa_kuota , ifnull(max(fee),0) *ifnull(t_pendaftar.total_pendaftar,0) as total_uang_masuk  
                    FROM `archery_event_category_details` 
                    left join archery_master_team_categories on archery_master_team_categories.id = archery_event_category_details.team_category_id 
                    left join (select tc.team_name,count(p.team_category_id) as total_pendaftar from archery_event_participants p left join archery_master_team_categories tc on tc.id=p.team_category_id where event_id=:event_id group by tc.team_name)t_pendaftar on t_pendaftar.team_name= archery_master_team_categories.team_name WHERE event_id =:event_id2 group by team_name', ['event_id' => $event_id,'event_id2' => $event_id]);

        $jenis_kelamin=DB::select("SELECT if(gender='male','Putra','Putri') as gender , count(id) as total FROM `archery_event_participants` where event_id=:event_id group by gender",['event_id' => $event_id]);
        
        //if(empty($jenis_regu || $nama_regu || $jenis_kelamin)){
        //    throw new BLoCException("data tidak ditemukan");
        //}
        
        $data_jenis_regu = [];
        
        foreach($jenis_regu as $key => $value){
            $data_jenis_regu[]=[
                'team_category_id' => $value->team_category_id,
                'fee' => $value->fee,
                'total' => $value->total ,
                'total_terjual' => $value->total_terjual,
                'sisa_kuota' => $value->sisa_kuota,
                'total_uang_masuk' => $value->total_uang_masuk,
                'event_name' => $value->event_name,
                'event_start_datetime' => $value->event_start_datetime,
                'event_end_datetime' => $value->event_end_datetime
            
            ];
        }
                
        $data_nama_regu = [];
        
        foreach($nama_regu as $key => $value){
            $data_nama_regu[]=[
                'team_name' => $value->team_name,
                'harga' => $value->harga,
                'total_kuota' => $value->total_kuota ,
                'total_pendaftar' => $value->total_pendaftar ,
                'sisa_kuota' => $value->sisa_kuota,
                'total_uang_masuk' => $value->total_uang_masuk,
            
            ];
        }

        $data_jenis_kelamin = [];
        
        foreach($jenis_kelamin as $key => $value){
            $data_jenis_kelamin[]=[
                'gender' => $value->gender,
                'total' => $value->total,
            ];
        }
        
        $nomor_pertandingan = array(
            array('Barebow' => array('Biaya' => array('Individu Putra/Putri' => 'RP.45454','Beregu Putra/Putri' => 'RP.45454','Beregu Campuran' => 'rp.56456'),'Kuota' => array('Individu Putra/Putri' => '32123','Beregu Putra/Putri' => '312','Beregu Campuran' => '1'),'Terjual' => array('Individu Putra/Putri' => '43','Beregu Putra/Putri' => '64','Beregu Campuran' => '54'),'Sisa Kuota' => array('Individu Putra/Putri' => '64','Beregu Putra/Putri' => '4','Beregu Campuran' => '34'),'Total uang masuk' => 'RP.6436346')),
            array('Compound' => array('Biaya' => array('Individu Putra/Putri' => 'RP.45454','Beregu Putra/Putri' => 'RP.45454','Beregu Campuran' => 'RP.45454'),'Kuota' => array('Individu Putra/Putri' => '323','Beregu Putra/Putri' => '23','Beregu Campuran' => '1'),'Terjual' => array('Individu Putra/Putri' => '34','Beregu Putra/Putri' => '64','Beregu Campuran' => '45'),'Sisa Kuota' => array('Individu Putra/Putri' => '46','Beregu Putra/Putri' => '6','Beregu Campuran' => '43'),'Total uang masuk' => 'RP.6436346')),
            array('Nasional' => array('Biaya' => array('Individu Putra/Putri' => 'RP.45454','Beregu Putra/Putri' => 'RP.45454','Beregu Campuran' => 'RP.45454'),'Kuota' => array('Individu Putra/Putri' => '231','Beregu Putra/Putri' => '2','Beregu Campuran' => '1'),'Terjual' => array('Individu Putra/Putri' => '54','Beregu Putra/Putri' => '46','Beregu Campuran' => '54'),'Sisa Kuota' => array('Individu Putra/Putri' => '4','Beregu Putra/Putri' => '2','Beregu Campuran' => '6'),'Total uang masuk' => 'RP.6436346')),
            array('Recurve' => array('Biaya' => array('Individu Putra/Putri' => 'RP.45454','Beregu Putra/Putri' => 'RP.45454','Beregu Campuran' => 'RP.45454'),'Kuota' => array('Individu Putra/Putri' => '31','Beregu Putra/Putri' => '1','Beregu Campuran' => '1'),'Terjual' => array('Individu Putra/Putri' => '646','Beregu Putra/Putri' => '64','Beregu Campuran' => '46'),'Sisa Kuota' => array('Individu Putra/Putri' => '46','Beregu Putra/Putri' => '6','Beregu Campuran' => '8'),'Total uang masuk' => 'RP.6436346')),
        );

        $ringkasan_umum = array('Kategori' =>array(
            array('Master - Compound - 50m' => array('Individu Putra' => array('Terisi' => '543','Total Kuota' => '21','Sisa Kuota' => '3'),'Individu Putri' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Putra' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Putri' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Campuran' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'))),
            array('Master - Recurve - 70m' => array('Individu Putra' => array('Terisi' => '21','Total Kuota' => '21','Sisa Kuota' => '3'),'Individu Putri' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Putra' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Putri' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Campuran' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'))),
            array('U-12 - Nasional - 15m' => array('Individu Putra' => array('Terisi' => '212','Total Kuota' => '3','Sisa Kuota' => '3'),'Individu Putri' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Putra' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Putri' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'),'Beregu Campuran' => array('Terisi' => 'RP.45454','Total Kuota' => 'RP.45454','Sisa Kuota' => 'rp.56456'))),
           ));

       
     
        return view('reports.ringkasan_peserta', [
            'data_jenis_regu' => $data_jenis_regu,
            'data_nama_regu' => $data_nama_regu,
            'data_jenis_kelamin' => $data_jenis_kelamin,
            'data_nomor_pertandingan'=> $nomor_pertandingan,
            'data_ringkasan_umum'=> $ringkasan_umum,
            'event_name'=> strtoupper($data_jenis_regu[0]['event_name']),
            'event_start_datetime' => $data_jenis_regu[0]['event_start_datetime'],
            'event_end_datetime' => $data_jenis_regu[0]['event_end_datetime'],
        ]);
    }

    public function headings(): array
    {
        return [
            'A' =>200,
            'B' => 200, 
            'C' => 200          
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,            
            'C' => 20,   
            'D' => 30,
            'E' => 30,
            'F' => 20,
            'G' => 30,
            'H' => 30,
            'I' => 25,
            'J' => 20,
            'K' => 30,
            'L' => 30,
            'M' => 25,
            'N' => 30,
            'O' => 30,
            'P' => 20,
            'Q' => 30,
        ];
    }
    
}


