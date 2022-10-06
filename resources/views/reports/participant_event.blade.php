<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>DAFTAR PARTISIPAN EVENT</title>


    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9"
            style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">

            <strong>DAFTAR PARTISIPAN SUDAH BAYAR PADA EVENT {{ $event_name }}</strong>
        </td>

    </table>



    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th style="text-align: center; background: #FFFF00;"><strong>KODE KATEGORI</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>KODE ATLET</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>DIVISI KATEGORI INDIVIDU</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Pemeringkatan</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Status Verifikasi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Timestamp</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Email Address</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA LENGKAP</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>GENDER</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Kewarganegaraan</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>ALAMAT LENGKAP</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>PROVINSI DOMISILI</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>KOTA DOMISILI</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TEMPAT TANGGAL LAHIR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>USIA PER {{ $event_start_date }}</strong>
                </th>
                <th style="text-align: center; background: #FFFF00;"><strong>NOMOR HP WA AKTIF</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NOMOR KTP/NIK (16 DIGIT)</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Negara (Asing)</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Kota Negara (Asing)</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Nomor Passport (Asing)</strong></th>
                {{-- <th style="text-align: center; background: #FFFF00;"><strong>FOTO KTP (WAJIB BAGI DOMISILI DKI
                        JAKARTA)</strong></th> --}}
                {{-- <th style="text-align: center; background: #FFFF00;"><strong>FOTO PESERTA</strong></th> --}}
                {{-- <th style="text-align: center; background: #FFFF00;"><strong>FOTO BUKTI TRANSFER</strong></th> --}}
                <th style="text-align: center; background: #FFFF00;"><strong>KLUB BERLATIH</strong></th>
            </tr>
            @foreach ($datas as $data)
                <tr>
                    <td style="text-align: center;">{{ $data['category_code'] ? $data['category_code'] : '-' }}</td>
                    <td style="text-align: center;">{{ $data['athlete_code'] ? $data['athlete_code'] : '-' }}</td>
                    <td style="text-align: left;">{{ $data['category'] ? $data['category'] : '-' }} </td>
                    <td style="text-align: center;">{{ $data['is_series'] && $data['is_series'] == 1 ? '√' : '' }}</td>
                    <td style="text-align: center;">
                        {{ $data['verify_status'] && $data['verify_status'] == 1 ? '√' : '' }}</td>
                    <td style="text-align: center;">{{ $data['timestamp'] ? $data['timestamp'] : '-' }}</td>
                    <td style="text-align: center;">{{ $data['email'] ? $data['email'] : '-' }}</td>
                    <td style="text-align: center;">{{ $data['full_name'] ? $data['full_name'] : '-' }}</td>
                    <td style="text-align: left;">{{ $data['gender'] ? $data['gender'] : '-' }}</td>
                    <td style="text-align: left;">{{ $data['nationality'] ? $data['nationality'] : '-' }}</td>
                    <td style="text-align: center;">{{ $data['address'] ? $data['address'] : '-' }}</td>
                    <td style="text-align: left;">{{ $data['province'] ? $data['province'] : '-' }}</td>
                    <td style="text-align: left;">{{ $data['city'] ? $data['city'] : '-' }} </td>
                    <td style="text-align: left;">{{ $data['date_of_birth'] }}</td>
                    <td style="text-align: left;">{{ $data['age'] ? $data['age'] : '-' }} </td>
                    <td style="text-align: left;">{{ $data['phone_number'] ? $data['phone_number'] : '-' }}</td>
                    <td style="text-align: center;">{{ $data['nik'] ? $data['nik'] : '-' }} </td>
                    <td style="text-align: center;">{{ $data['country'] ? $data['country'] : '-' }} </td>
                    <td style="text-align: center;">{{ $data['city_of_country'] ? $data['city_of_country'] : '-' }} </td>
                    <td style="text-align: center;">{{ $data['passport_number'] ? $data['passport_number'] : '-' }} </td>
                    {{-- <td style="text-align: center;">{{ $data['foto_ktp'] ? $data['foto_ktp'] : '-' }} </td> --}}
                    {{-- <td style="text-align: center;">{{ $data['foto_peserta'] ? $data['foto_peserta'] : '-' }} </td> --}}
                    {{-- <td style="text-align: center;">{{ $data['foto_bukti'] ? $data['foto_bukti'] : '-' }} </td> --}}
                    <td style="text-align: left;">{{ $data['club'] ? $data['club'] : '-' }} </td>
                </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
