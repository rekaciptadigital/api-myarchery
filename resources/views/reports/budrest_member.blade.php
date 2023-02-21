<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   
    <title>DAFTAR BANTALAN</title>
</head>

<body>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9"
            style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">

            <strong>{{ $datas[0]['label_category'] }}</strong>
        </td>

    </table>



    <table style="border: 1px solid black;">
        <tbody>
            <tr style="border: 1px solid black;">
                <th style="text-align: center; background: #FFFF00;border: 1px solid black;">
                    <strong>BANTALAN</strong>
                </th>
                <th style="text-align: center; background: #FFFF00;border: 1px solid black;">
                    <strong>NAMA</strong>
                </th>
                <th style="text-align: center; background: #FFFF00;border: 1px solid black;">
                    <strong>KLUB/KONTINGEN</strong>
                </th>
            </tr>
            @foreach ($datas as $data)
                <tr style="border: 1px solid black;">
                    <td style="text-align: center;border: 1px solid black;">
                        {{ $data['bud_rest_number'] }}
                    </td>
                    <td style="text-align: center;border: 1px solid black;">
                        {{ $data['name'] }}
                    </td>
                    <td style="text-align: center;border: 1px solid black;">
                        {{ $data['with_contingent'] == 0 ? $data['club_name'] : $data['city_name'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
