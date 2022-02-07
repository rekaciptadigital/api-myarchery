<?php

namespace App\Exports;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\User;
use App\Models\ArcheryEventIdcardTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryUserAthleteCode;
use Illuminate\Support\Facades\Auth;

class ArcheryEventParticipantStatusLunasPendingAll implements FromView, WithColumnWidths, WithHeadings
{
  
    public function view(): View
    {
        $admin = Auth::user();
        
        
        $data= ArcheryEventParticipant::select('archery_event_participants.created_at','email','name','phone_number','team_category_id')
        ->leftJoin("archery_events", "archery_events.id", "=", "archery_event_participants.event_id")
        ->where('archery_event_participants.status',4)->where('archery_events.admin_id',$admin->id)->get();

        if ($data->isEmpty()){
            throw new BLoCException("data untuk event tersebut tidak ditemukan untuk status pembayaran lunas");
        }
      

        $export_data = [];
        
        foreach($data as $key => $value){
            $kode_kategori=ArcheryEventIdcardTemplate::getCategoryLabel($value->id, $value->user_id);
            $user=User::select('date_of_birth','ktp_kk','selfie_ktp_kk','nik',DB::RAW("TIMESTAMPDIFF(YEAR, date_of_birth, '2022-03-03') AS age"))->where('id',$value->user_id)->first();
            $kode_atlet=ArcheryUserAthleteCode::getAthleteCode($value->user_id);
           

            $export_data[] = [
                'kode_kategori' => $kode_kategori,
                'kode_atlet' => $kode_atlet ? $kode_atlet: '-',
                'timestamp' => $value->created_at,
                'email' => $value->email,
                'nama_lengkap' => $value->name,
                'gender' => $value->gender,
                'address' =>'-',
                'date_of_birth' => $user['date_of_birth']? $user['date_of_birth'] : '-',
                'age' => $user['age'] ? $user['age'] : '-',
                'phone_number' => $value->phone_number,
                'provinsi_domisili' => '-',
                'kota_domisili' => '-',
                'nik' => $user['nik']? $user['nik'] : '-',
                'divisi_kategori_individu' => $value->team_category_id,
                'foto_peserta' => $user['selfie_ktp_kk']? $user['selfie_ktp_kk'] : '-',
                'foto_ktp' => $user['ktp_kk']? $user['ktp_kk'] : '-',
                'foto_bukti' => '-',
               

                
            ];
        }
        
        
        
                
        return view('reports.participant_event_pending_all', [
            'datas' => $export_data,
            'event_name'=> '-'

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


