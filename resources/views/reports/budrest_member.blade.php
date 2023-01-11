<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   
    <title>DAFTAR BANTALAN</title>


    <style>
        /* table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        } */
    </style>
</head>

<body>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9"
            style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">

            <strong>{{ $datas[0]['label_category'] }}</strong>
        </td>

    </table>



    <table style="border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr style="border: 1px solid black;">
                <th style="text-align: center; background: #FFFF00;border: 1px solid black;"><strong>BANTALAN</strong>
                </th>
                <th style="text-align: center; background: #FFFF00;border: 1px solid black;"><strong>NAMA</strong></th>
                <th style="text-align: center; background: #FFFF00;border: 1px solid black;"><strong>KLUB</strong></th>
            </tr>
            @foreach ($datas as $data)
                <tr style="border: 1px solid black;">
                    <td style="text-align: center;border: 1px solid black;">{{ $data['bud_rest_number'] }}</td>
                    <td style="text-align: center;border: 1px solid black;">{{ $data['name'] }}</td>
                    <td style="text-align: center;border: 1px solid black;">{{ $data['club_name'] }}</td>
                </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
