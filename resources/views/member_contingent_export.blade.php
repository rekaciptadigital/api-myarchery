<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
    <table style="width:100%;border: 1px solid black;">
        <thead>
            <tr>
                <th style="text-align: center;"><strong>Nama</strong></th>
                <th style="text-align: center;"><strong>Tanggal Lahir</strong></th>
                <th style="text-align: center;"><strong>Email</strong></th>
                <th style="text-align: center;"><strong>Gender</strong></th>
                <th style="text-align: center;"><strong>NO HP</strong></th>
                <th style="text-align: center;"><strong>Kategori ID</strong></th>
                <th style="text-align: center;"><strong>Kota ID</strong></th>
                <th style="text-align: center;"><strong>NO.Surat Rekomendasi</strong></th>
                <th style="text-align: center;"><strong>KTP/KK</strong></th>
                <th style="text-align: center;"><strong>Surat Binaan</strong></th>
                <th style="text-align: center;"><strong>Nama Penanggung Jawab</strong></th>
                <th style="text-align: center;"><strong>No HP Penanggung Jawab</strong></th>
                <th style="text-align: center;"><strong>Email Penanggung Jawab</strong></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td style="text-align: center;">{{ $d['name'] }}</td>
                    <td style="text-align: center;">{{ $d['date_of_birth'] }}</td>
                    <td style="text-align: center;">{{ $d['email'] }}</td>
                    <td style="text-align: center;">{{ $d['gender'] }}</td>
                    <td style="text-align: center;">{{ $d['phone_number'] }}</td>
                    <td style="text-align: center;">{{ $d['category_id'] }}</td>
                    <td style="text-align: center;">{{ $d['city_id'] }}</td>
                    <td style="text-align: center;">{{ $d['no_recomendation_later'] }}</td>
                    <td style="text-align: center;">{{ $d['ktp_kk'] }}</td>
                    <td style="text-align: center;">{{ $d['binaan_later'] }}</td>
                    <td style="text-align: center;">{{ $d['responsible_name'] }}</td>
                    <td style="text-align: center;">{{ $d['responsible_phone_number'] }}</td>
                    <td style="text-align: center;">{{ $d['responsible_email'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
