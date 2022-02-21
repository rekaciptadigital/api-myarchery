<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        table.detail-athlete td {
            border: none;
            text-align: start;
            float: left;
        }

        table.table-scorer {
            width: 100%;
        }

        table.table-scorer,
        th,
        td {
            /* border: 1px solid black; */
            border-collapse: collapse;
            text-align: center;
            height: 30px;
        }

        .border {
            border: 1px solid black;
        }

    </style>
    <title>Document</title>
</head>

<body>
    <table class="detail-athlete">
        <tr>
            <td>Athlete</td>
            <td>:</td>
            <td>{{ $data['detail_member']['name'] }}</td>
            <td>Event</td>
            <td>:</td>
            <td>{{ $event['event_name'] }}</td>
        </tr>
        <tr>
            <td>Club</td>
            <td>:</td>
            <td>{{ $data['detail_member']['club_name'] }}</td>
            <td>Lokasi</td>
            <td>:</td>
            <td>{{ $event['detail_city']['name'] }}</td>
        </tr>
        <tr>
            <td>No. Bantalan</td>
            <td>:</td>
            <td>{{ $data['detail_member']['bud_rest_number'] }}</td>
            <td>Category</td>
            <td>:</td>
            <td>{{ $category_label }}</td>
        </tr>
        <tr>
            <td>Sesi</td>
            <td>:</td>
            <td>{{ $data['sesi'] }}</td>
            <td>Code</td>
            <td>:</td>
            <td>{{ $data['code'] }}</td>
        </tr>
    </table>

    <table class="table-scorer">
        <thead>
            <tr>
                <th style="font-size: 11px; width:8%;">{{ $category['distance_id'] }}-{{ $data['sesi'] }}</th>
                <th class="border">1</th>
                <th class="border">2</th>
                <th class="border">3</th>
                <th class="border">SUM</th>
                <th class="border" style="width: 20%;">Total</th>
                <th class="border">10+x</th>
                <th class="border">x</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border" rowspan="2">1</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border"></td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border" rowspan="2">2</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border" rowspan="2">3</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border" rowspan="2">4</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border" rowspan="2">5</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border" rowspan="2">6</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
            <tr>
                <td class="border" style="border: none;" colspan="5"> <span
                        style="margin-right: 20px;">total</span></td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
                <td class="border">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <img src="{{$qr}}">
    
    <pagebreak />
</body>

</html>
