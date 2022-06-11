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
    <table style="height: 276px; width: 549px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr style="height: 30px;">
                <td style="width: 150px; height: 86px; border-style: none; text-align: center;">
                    <img style="display: block;" src="https://myarchery.id/static/media/myachery.9ed0d268.png" alt=""
                        height="90" />
                </td>
                <td
                    style="width: 349px; height: 86px; border-style: none; font-size: 12px;text-align: left;float: left;">
                    <p style="text-align: left;float: left;">
                    <h3>{{ $event['event_name'] }}</h3>
                    </p>
                    <p style="text-align: left;float: left;">{{ $category_label }}</p>
                </td>
                <td style="width: 150px; height: 86px; border-style: none; text-align: center;">
                    <img style="display: block;"
                        src="https://api.myarchery.id/asset/poster/poster_JAKARTA%20SERIES%20I%20ARCHERY%20COMPETITION%202022.png#1644059999"
                        alt="" height="90" />
                </td>
            </tr>
        </tbody>
    </table>

    <table style="height: 276px; width: 549px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr style="height: 60px;text-align: left;float: left;">
                <td style="border-bottom: 1pt solid black;width: 00px; height: 30px;text-align: left;float: left;">Nama
                </td>
                <td style="border-bottom: 1pt solid black;width: 9px; text-align: center; height: 30px;">:</td>
                <td style="border-bottom: 1pt solid black;width: 340px; height: 30px;text-align: left;float: left;"
                    colspan="3">{{ $data['detail_member']['name'] }}</td>
                <td style="width: 30px; text-align: center; height: 10px;"></td>
                <td rowspan="2" style="padding:10px;background-color: #e3e2de; width: 100px; height: 30px;">
                    <h3>{{ $data['detail_member']['bud_rest_number'] != 0 ? $data['detail_member']['bud_rest_number'] : '' }}{{ $data['detail_member']['target_face'] }}
                    </h3>
                </td>
            </tr>
            <tr style="height: 60px;text-align: left;float: left;">
                <td style="border-bottom: 1pt solid black;width: 100px; height: 30px;text-align: left;float: left;">Klub
                </td>
                <td style="border-bottom: 1pt solid black;width: 9px; text-align: center; height: 30px;">:</td>
                <td style="border-bottom: 1pt solid black;width: 340px; height: 30px;text-align: left;float: left;"
                    colspan="3">{{ $data['detail_member']['club_name'] }}</td>
            </tr>
        </tbody>
    </table>
    <br />
    <table class="table-scorer" style="height: 276px; width: 549px;">
        <thead>
            <tr style="height: 42px;">
                <th style="font-size: 11px; width: 17px; height: 42px; text-align: left;">
                    {{ $data['sesi'] }}</th>
                <th class="border" style="height: 42px; width: 20px;">1</th>
                <th class="border" style="height: 42px; width: 20px;">2</th>
                <th class="border" style="height: 42px; width: 20px;">3</th>
                <th class="border" style="height: 42px; width: 50.2;">Jumlah</th>
                <th class="border" style="width: 50.3px; height: 42px;">Total</th>
                <th class="border" style="height: 42px; width: 50.01px;">10+x</th>
                <th class="border" style="height: 42px; width: 50px;">x</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #e3e2de;">
                <td class="border" style="height: 36px; width: 17px;" rowspan="2">1</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.3px;" rowspan="2">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="background-color: #e3e2de;">
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="height: 36px; width: 17px; text-align: center;" rowspan="2">2</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.3px;" rowspan="2">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="background-color: #e3e2de;">
                <td class="border" style="height: 36px; width: 17px; text-align: center;" rowspan="2">3</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.3px;" rowspan="2">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="background-color: #e3e2de;">
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="height: 36px; width: 17px; text-align: center;" rowspan="2">4</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.3px;" rowspan="2">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="background-color: #e3e2de;">
                <td class="border" style="height: 36px; width: 17px; text-align: center;" rowspan="2">5</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.3px;" rowspan="2">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="background-color: #e3e2de;">
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="height: 36px; width: 17px; text-align: center;" rowspan="2">6</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.3px;" rowspan="2">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 20px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.2;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50.01px;">&nbsp;</td>
                <td class="border" style="height: 30px; width: 50px;">&nbsp;</td>
            </tr>
            <tr style="height: 60px;">
                <td class="border" style="width: 164px; height: 30px;" colspan="4">kode : {{ $data['code'] }}
                </td>
                <td class="border" style="width: 50.2; height: 30px;"><strong>Total</strong></td>
                <td class="border" style="width: 50.3px; height: 30px;">&nbsp;</td>
                <td class="border" style="width: 50.3px; height: 30px;">&nbsp;</td>
                <td class="border" style="width: 50.3px; height: 30px;">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <table style="height: 276px; width: 549px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td style="border-bottom: 1pt solid black;width: 39.1621%; height: 100px;">&nbsp;</td>
                <td rowspan="2" style="width: 23.1329%; height: 10px;">
                    <img style="display: block;" src="{{ $qr }}" alt="" width="90" height="90" />
                </td>
                <td style="border-bottom: 1pt solid black;width: 37.705%; height: 100px;">&nbsp;</td>
            </tr>
            <tr style="height: 15px;">
                <td style="width: 39.1621%; height: 14px; text-align: center;">wasit</td>
                <td style="width: 37.705%; height: 14px; text-align: center;">peserta</td>
            </tr>
        </tbody>
    </table>
    <pagebreak />
</body>

</html>
