<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>{{ substr(str_replace('Individu', '', $category), 0, 30) }}</title>


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
        <td colspan="9" style="text-align: center;">
            <strong>
                <h1>{{ $category }}</h1>
            </strong>
        </td>

    </table>



    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
            <strong>
                {{ $category }}
            </strong>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th style="text-align: center; background: #FFFF00;"><strong>Peringkat</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Nama</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>tgl lahir</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>email</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Asal Kota Madya</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Poin Kualifikasi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Poin Eliminasi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Point</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Score Kualifikasi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total x-y Kualifikasi</strong></th>
                @foreach ($datas[0]['total_per_series'] as $item)
                    <th style="text-align: center; background: #FFFF00;"><strong>{{ $item['event_name'] }}</strong></th>
                    <th style="text-align: center; background: #FFFF00;"><strong>Qualification</strong></th>
                    <th style="text-align: center; background: #FFFF00;"><strong>Elimination</strong></th>
                @endforeach
            </tr>
            @foreach ($datas as $data)
                <tr>
                    <td style="text-align: center;">{{ $data['pos'] ? $data['pos'] : '-' }}</td>
                    <td style="text-align: left;">{{ $data['name'] ? $data['name'] : '-' }} </td>
                    <td style="text-align: left;">{{ $data['date_of_birth'] ? $data['date_of_birth'] : '' }}</td>
                    <td style="text-align: left;">{{ $data['email'] ? $data['email'] : '' }}</td>
                    <td style="text-align: left;">{{ $data['city'] ? $data['city'] : '' }}</td>
                    <td style="text-align: center;">
                        {{ $data['point_qualification'] ? $data['point_qualification'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['point_elimination'] ? $data['point_elimination'] : '0' }}
                    </td>
                    <td style="text-align: center;">{{ $data['total_point'] ? $data['total_point'] : '0' }}</td>
                    <td style="text-align: center;">
                        {{ $data['total_score_qualification'] ? $data['total_score_qualification'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['x_y_qualification'] ? $data['x_y_qualification'] : '0' }}
                    </td>
                    @foreach ($data['total_per_series'] as $item)
                        <td style="text-align: center;">
                            {{ $item['total'] != null ? $item['total'] : 'null' }}
                        </td>
                        @php
                            $qualification = isset($item['point_details']['qualification']) ? $item['point_details']['qualification'] : null;
                            $elimination = isset($item['point_details']['elimination']) ? $item['point_details']['elimination'] : null;
                        @endphp
                        <td style="text-align: center;">
                            {{ $qualification != null ? $qualification : 'null' }}
                        </td>
                        <td style="text-align: center;">
                            {{ $elimination != null ? $elimination : 'null' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
