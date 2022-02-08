<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    @if($status_id ==1)    
    <title>DAFTAR PARTISIPAN SUDAH BAYAR</title>
    @else
    <title>DAFTAR PARTISIPAN BELUM BAYAR</title>     
    @endif
   
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
                    @if($status_id ==1)    
                    <strong>DAFTAR PARTISIPAN SUDAH BAYAR {{$event_name}}</strong></td>
                    @else
                    <strong>DAFTAR PARTISIPAN BELUM BAYAR {{$event_name}}</strong></td>     
                    @endif
    </table>



    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; "><strong>KODE KATEGORI</strong></th>
                <th style="text-align: center; "><strong>KODE ATLET</strong></th>
                <th style="text-align: center; "><strong>Timestamp</strong></th>
                <th style="text-align: center; "><strong>Email Address</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA LENGKAP</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>GENDER</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>ALAMAT LENGKAP</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TEMPAT TANGGAL LAHIR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>USIA PER 3 MARET 2022</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NOMOR HP WA AKTIF</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>PROVINSI DOMISILI</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>KOTA DOMISILI</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NOMOR KTP/NIK (16 DIGIT)</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>FOTO KTP (WAJIB BAGI DOMISILI DKI JAKARTA)</strong></th>
                <th style="text-align: center; "><strong>DIVISI KATEGORI INDIVIDU</strong></th>
                <th style="text-align: center; "><strong>FOTO PESERTA</strong></th>
                <th style="text-align: center; "><strong>FOTO BUKTI TRANSFER</strong></th>
            </tr>
            @foreach ($datas as $data)
            <tr>
                <td style="text-align: center;">{{ $data['kode_kategori'] ? $data['kode_kategori'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['kode_atlet'] ? $data['kode_atlet'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['timestamp'] ? $data['timestamp'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['email'] ? $data['email'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['nama_lengkap'] ? $data['nama_lengkap'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['gender'] ? $data['gender'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['address'] ? $data['address'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['date_of_birth'] ? $data['date_of_birth'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['age'] ? $data['age'] : '-' }} tahun </td>
                <td style="text-align: center;">{{ $data['phone_number'] ? $data['phone_number'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['provinsi_domisili'] ? $data['provinsi_domisili'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['kota_domisili'] ? $data['kota_domisili'] : '-' }}  </td>
                <td style="text-align: center;">{{ $data['nik'] ? $data['nik'] : '-' }}  </td>
                <td style="text-align: center;">{{ $data['foto_ktp'] ? $data['foto_ktp'] : '-' }}  </td>
                <td style="text-align: center;">{{ $data['divisi_kategori_individu'] ? $data['divisi_kategori_individu'] : '-' }}  </td>
                <td style="text-align: center;">{{ $data['foto_peserta'] ? $data['foto_peserta'] : '-' }}  </td>
                <td style="text-align: center;">{{ $data['foto_bukti'] ? $data['foto_bukti'] : '-' }}  </td>
               
            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
