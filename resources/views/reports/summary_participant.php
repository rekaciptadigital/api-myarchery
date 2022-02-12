<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   
    <title>RINGKASAN PESERTA</title>
    
   
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>
</head>

<body>
<table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">
                     
                    <strong>Ringkasan Peserta EVENT {{$event->event_name}}</strong></td>
                   
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>Mulai dari tanggal {{$event->event_start_datetime}} - {{$event->event_end_datetime}}</p></td>   
    </table>
    <table border="4" style="border:1px solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>   
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>Berdasarkan Jenis Regu</p></td>   
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>No</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Kategori</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Harga Regis</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Quota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Terjual</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Sisa Kuota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Uang Masuk</strong></th>
              
            </tr>
            <?php
                $no = 0;
            ?>
            @foreach ($team_category as $data)
            <tr>
                <td style="text-align: center;">{{ $no = $no + 1 }}</td>
                <td style="text-align: center;">{{ $data['label'] ? $data['label'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['fee'] ? $data['fee'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['quota'] ? $data['quota'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['total_sell'] ? $data['total_sell'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['left_quota'] ? $data['left_quota'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['total_amount'] ? $data['total_amount'] : '0' }}</td>
            </tr>
            @endforeach
            
            <tr>
                <td colspan="3" style="text-align: center;">Total</td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
            </tr>
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>

    <table border="4" style="border:1px solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>   
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>Berdasarkan Regu</p></td>   
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>No</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA REGU</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>HARGA DAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL KUOTA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL PENDAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SISA KUOTA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL UANG MASUK</strong></th>
              
            </tr>
            <?php
                $no = 0;
            ?>
            @foreach ($team as $key => $data)
            <tr>
                <td style="text-align: center;">{{ $no = $no+1 }}</td>
                <td style="text-align: center;">{{ $key }}</td>
                <td style="text-align: center;">{{ $data['amount'] ? $data['amount'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['quota'] ? $data['quota'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['quota_sell'] ? $data['quota_sell'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['left_quota'] ? $data['left_quota'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['total_amount'] ? $data['total_amount'] : '0' }}</td>     
            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
    <table border="4" style="border:1px solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>   
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>Berdasarkan Nomor Pertandingan</p></td>   
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA REGU</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>HARGA DAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL KUOTA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL PENDAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SISA KUOTA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL UANG MASUK</strong></th>
              
            </tr>
           
          
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
    <table border="4" style="border:1px solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>   
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>Berdasarkan Jenis Kelamin</p></td>   
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>JENIS KELAMIN</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL PENDAFTAR</strong></th>
              
              
            </tr>
            @foreach ($data_jenis_kelamin as $data)
            <tr>
                <td style="text-align: center;">{{ $data['gender'] ? $data['gender'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['total'] ? $data['total'] : '0' }}</td>
                
               
            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
    <table border="4" style="border:1px solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>   
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>Berdasarkan Ringkasan Umum</p></td>   
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA REGU</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>HARGA DAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL KUOTA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL PENDAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SISA KUOTA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL UANG MASUK</strong></th>
              
            </tr>
           
          
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>

</body>

</html>
