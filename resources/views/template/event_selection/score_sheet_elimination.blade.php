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
            font-size: 10pt;
        }

        table.table-scorer {
            width: 100%;
            font-size: 10pt;
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
    <table style="height: 376px; width: 869px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr style="height: 30px;">
                <td style="width: 150px; height: 86px; border-style: none; text-align: center;">
                    <img style="display: block;" src="https://myarchery.id/static/media/myachery.9ed0d268.png"
                        alt="" height="90" />
                </td>
                <td
                    style="width: 349px; height: 86px; border-style: none; font-size: 12px;text-align: left;float: left;">
                    <p style="text-align: left;float: left;">
                    <h3>{{ $event['event_name'] }}</h3>
                    </p>
                    <p style="text-align: left;float: left;">{{ $category_label }}</p>
                </td>
                <td style="width: 150px; height: 86px; border-style: none; text-align: center;">
                    <img style="display: block;" src="{{ $event['logo'] }}" alt="" height="90" />
                </td>
            </tr>
        </tbody>
    </table>
    <br><br>
    <table style="height: 376px; width: 869px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr style="height: 60px;text-align: left;float: left;">
                <td
                    style="border-bottom: 1pt solid black;width: 00px; height: 30px;text-align: left;float: left;font-size:10pt">
                    Nama
                </td>
                <td style="border-bottom: 1pt solid black;width: 9px; text-align: center; height: 30px;">:</td>
                <td style="border-bottom: 1pt solid black;width: 340px; height: 30px;text-align: left;float: left; font-size:10pt"
                    colspan="3">
                    {{ $data['detail_member']['name'] }}
                </td>
                <td style="width: 30px; text-align: center; height: 10px;"></td>
                <td rowspan="2" style="padding:10px;background-color: #e3e2de; width: 100px; height: 30px;">
                    <h3>
                        {{ $data['detail_member']['bud_rest_number'] != 0 ? $data['detail_member']['bud_rest_number'] : '' }}{{ $data['detail_member']['target_face'] }}
                    </h3>
                </td>
            </tr>
            <tr style="height: 60px;text-align: left;float: left;">
                <td
                    style="border-bottom: 1pt solid black;width: 100px; height: 30px;text-align: left;float: left; font-size:10pt">
                    Klub/Kontingen
                </td>
                <td style="border-bottom: 1pt solid black;width: 9px; text-align: center; height: 30px;">:</td>
                <td style="border-bottom: 1pt solid black;width: 340px; height: 30px;text-align: left;float: left;font-size:10pt"
                    colspan="3">
                    @if ($data['detail_member']['parent_classification_type'] == 2)
                        {{ ucwords(strtolower($data['detail_member']['country_name'])) }}
                    @elseif ($data['detail_member']['parent_classification_type'] == 3)
                        {{ ucwords(strtolower($data['detail_member']['province_name'])) }}
                    @elseif ($data['detail_member']['parent_classification_type'] == 4)
                        {{ ucwords(strtolower($data['detail_member']['city_name'])) }}
                    @elseif ($data['detail_member']['parent_classification_type'] > 5)
                        {{ ucwords(strtolower($data['detail_member']['children_classification_members_name'])) }}
                    @else
                        {{ ucwords(strtolower($data['detail_member']['club_name'])) }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
    <br />
    <table class="table-scorer" style="height: 376px; width: 869px;">
        <thead>
            <tr style="height: 42px;">
                <th style="font-size: 15px; width: 17px; height: 42px; text-align: center;">
                    {{ $data['sesi'] }}</th>
                <th class="border" style="height: 42px; width: 20px;">1</th>
                <th class="border" style="height: 42px; width: 20px;">2</th>
                <th class="border" style="height: 42px; width: 20px;">3</th>
                @if ($total_shot_per_stage > 6)
                    <th class="border" style="height: 42px; width: 20px;">4</th>
                @endif
                @if ($total_shot_per_stage > 8)
                    <th class="border" style="height: 42px; width: 20px;">5</th>
                @endif
                <th class="border" style="height: 42px; width: 50.2;">Jumlah</th>
                <th class="border" style="width: 50.3px; height: 42px;">Total</th>
                <th class="border" style="height: 42px; width: 50.01px;">10+x</th>
                <th class="border" style="height: 42px; width: 50px;">x</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $total_stage; $i++)
                <tr>
                    <td class="border" style="height: 75px; width: 17px;" rowspan="1">{{ $i + 1 }}</td>
                    <td class="border" style="height: 75px; width: 20px;">&nbsp;</td>
                    @if ($total_shot_per_stage > 2)
                        <td class="border" style="height: 75px; width: 20px;">&nbsp;</td>
                    @endif
                    @if ($total_shot_per_stage > 4)
                        <td class="border" style="height: 75px; width: 20px;">&nbsp;</td>
                    @endif
                    @if ($total_shot_per_stage > 6)
                        <td class="border" style="height: 75px; width: 20px;">&nbsp;</td>
                    @endif
                    @if ($total_shot_per_stage > 8)
                        <td class="border" style="height: 75px; width: 20px;">&nbsp;</td>
                    @endif
                    <td class="border" style="height: 75px; width: 50.2;">&nbsp;</td>
                    <td class="border" style="height: 75px; width: 50.3px;" rowspan="1">&nbsp;</td>
                    <td class="border" style="height: 75px; width: 50.01px;">&nbsp;</td>
                    <td class="border" style="height: 75px; width: 50px;">&nbsp;</td>
                </tr>
            @endfor
            <tr style="height: 60px;">
                <td class="border" style="width: 164px; height: 25px;" colspan="4">kode :
                    {{ $data['code'] }}
                </td>
                <td class="border" style="width: 50.2; height: 25px;"><strong>Total</strong></td>
                <td class="border" style="width: 50.3px; height: 25px;">&nbsp;</td>
                <td class="border" style="width: 50.3px; height: 25px;">&nbsp;</td>
                <td class="border" style="width: 50.3px; height: 25px;">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>
    <table style="height: 376px; width: 549px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td rowspan="2" style="width: 280px; height: 200px;">
                    <img style="display: block;" src="{{ $qr }}" alt="" width="150"
                        height="150" />
                </td>
                <td style="border-bottom: 1pt solid black;width: 39.1621%; height: 100px;margin :10px">&nbsp;</td>
                <td style="width: 10%; height: 100px;margin :10px">&nbsp;</td>
                <td style="border-bottom: 1pt solid black;width: 37.705%; height: 100px;margin :10px">&nbsp;</td>
            </tr>
            <tr style="height: 100px;">
                <td style="width: 39.1621%; height: 14px; text-align: center;margin :10px;">wasit</td>
                <td style="width: 150px; height: 14px; text-align: center;"> &nbsp; </td>
                <td style="width: 37.705%; height: 14px; text-align: center;margin :10px;">peserta</td>
            </tr>
        </tbody>
    </table>
    <pagebreak />
</body>

</html>
