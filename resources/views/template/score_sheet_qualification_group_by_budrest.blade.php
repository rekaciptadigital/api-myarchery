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

    <table>

        @foreach ($data as $group_member)
            <tr>
                @foreach ($group_member as $m)
                    <td style="padding-left:30px;padding-right:30px;">
                        <br>
                        <br>
                        <br>
                        <br>
                        <table style="height: 256px; width: 549px;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr style="height: 30px;">
                                    <td style="width: 150px; height: 86px; border-style: none; text-align: center;">
                                        <img style="display: block;"
                                            src="https://myarchery.id/static/media/myachery.9ed0d268.png" alt=""
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
                                        <img style="display: block;" src="{{ $event['logo'] }}" alt=""
                                            height="90" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table style="height: 246px; width: 549px;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr style="height: 60px;text-align: left;float: left;">
                                    <td
                                        style="border-bottom: 1pt solid black;width: 00px; height: 30px;text-align: left;float: left;">
                                        Nama
                                    </td>
                                    <td
                                        style="border-bottom: 1pt solid black;width: 9px; text-align: center; height: 30px;">
                                        :
                                    </td>
                                    <td style="border-bottom: 1pt solid black;width: 340px; height: 30px;text-align: left;float: left;"
                                        colspan="3">{{ $m['detail_member']['name'] }}</td>
                                    <td style="width: 30px; text-align: center; height: 10px;"></td>
                                    <td rowspan="2"
                                        style="padding:10px;background-color: #e3e2de; width: 100px; height: 30px;">
                                        <h3>{{ $m['detail_member']['bud_rest_number'] != 0 ? $m['detail_member']['bud_rest_number'] : '' }}{{ $m['detail_member']['target_face'] }}
                                        </h3>
                                    </td>
                                </tr>
                                <tr style="height: 60px;text-align: left;float: left;">
                                    <td
                                        style="border-bottom: 1pt solid black;width: 100px; height: 30px;text-align: left;float: left;">
                                        Klub/Kontingen
                                    </td>
                                    <td
                                        style="border-bottom: 1pt solid black;width: 9px; text-align: center; height: 30px;">
                                        :
                                    </td>
                                    <td style="border-bottom: 1pt solid black;width: 340px; height: 30px;text-align: left;float: left;"
                                        colspan="3">
                                        @if ($m['detail_member']['parent_classification_type'] == 2)
                                            {{ ucwords(strtolower($m['detail_member']['country_name'])) }}
                                        @elseif ($m['detail_member']['parent_classification_type'] == 3)
                                            {{ ucwords(strtolower($m['detail_member']['province_name'])) }}
                                        @elseif ($m['detail_member']['parent_classification_type'] == 4)
                                            {{ ucwords(strtolower($m['detail_member']['city_name'])) }}
                                        @elseif ($m['detail_member']['parent_classification_type'] > 5)
                                            {{ ucwords(strtolower($m['detail_member']['children_classification_members_name'])) }}
                                        @else
                                            {{ ucwords(strtolower($m['detail_member']['club_name'])) }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br />
                        <table class="table-scorer" style="height: 246px; width: 549px;">
                            <thead>
                                <tr style="height: 42px;">
                                    <th style="width: 17px; height: 42px; text-align: left;">
                                        {{ $m['sesi'] }}</th>
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
                                    <tr style="background-color: #e3e2de;">
                                        <td class="border" style="height: {{ $row_height }}; width: 17px;"
                                            rowspan="2">
                                            {{ $i + 1 }}
                                        </td>
                                        <td class="border" style="height: {{ $row_height }}; width: 20px;">&nbsp;
                                        </td>
                                        @if ($total_shot_per_stage > 2)
                                            <td class="border" style="height: {{ $row_height }}; width: 20px;">&nbsp;
                                            </td>
                                        @endif
                                        @if ($total_shot_per_stage >= 3)
                                            <td class="border" style="height: {{ $row_height }}; width: 20px;">&nbsp;
                                            </td>
                                        @endif
                                        @if ($total_shot_per_stage > 6)
                                            <td class="border" style="height: {{ $row_height }}; width: 20px;">&nbsp;
                                            </td>
                                        @endif
                                        @if ($total_shot_per_stage > 8)
                                            <td class="border" style="height: {{ $row_height }}; width: 20px;">&nbsp;
                                            </td>
                                        @endif
                                        <td class="border" style="height: {{ $row_height }}; width: 50.2;">&nbsp;
                                        </td>
                                        <td class="border" style="height: {{ $row_height }}; width: 50.3px;"
                                            rowspan="2">
                                            &nbsp;
                                        </td>
                                        <td class="border" style="height: {{ $row_height }}; width: 50.01px;">&nbsp;
                                        </td>
                                        <td class="border" style="height: {{ $row_height }}; width: 50px;">&nbsp;
                                        </td>
                                    </tr>
                                    <tr style="background-color: #e3e2de;">
                                        <td class="border"
                                            style="@if ($total_shot_per_stage < 4) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 20px;">
                                            &nbsp;
                                        </td>
                                        @if ($total_shot_per_stage > 2)
                                            <td class="border"
                                                style="@if ($total_shot_per_stage < 5) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 20px;">
                                                &nbsp;
                                            </td>
                                        @endif
                                        @if ($total_shot_per_stage >= 3)
                                            <td class="border"
                                                style="@if ($total_shot_per_stage < 6) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 20px;">
                                                &nbsp;
                                            </td>
                                        @endif
                                        @if ($total_shot_per_stage > 6)
                                            <td class="border" style="height: {{ $row_height }}; width: 20px;">&nbsp;
                                            </td>
                                        @endif
                                        @if ($total_shot_per_stage > 8)
                                            <td class="border"
                                                style="@if ($total_shot_per_stage < 10) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 20px;">
                                                &nbsp;
                                            </td>
                                        @endif
                                        <td class="border"
                                            style="@if ($total_shot_per_stage < 4) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 50.2;">
                                            &nbsp;
                                        </td>
                                        <td class="border"
                                            style="@if ($total_shot_per_stage < 4) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 50.01px;">
                                            &nbsp;
                                        </td>
                                        <td class="border"
                                            style="@if ($total_shot_per_stage < 4) echo 'background-color:black;'; @endif height: {{ $row_height }}; width: 50px;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                @endfor

                                <tr style="height: 60px;">
                                    <td class="border" style="width: 164px; height: {{ $row_height }};"
                                        colspan="4">kode :
                                        {{ $m['code'] }}
                                    </td>
                                    <td class="border" style="width: 50.2; height: {{ $row_height }};">
                                        <strong>Total</strong>
                                    </td>
                                    <td class="border" style="width: 50.3px; height: {{ $row_height }};">&nbsp;
                                    </td>
                                    <td class="border" style="width: 50.3px; height: {{ $row_height }};">&nbsp;
                                    </td>
                                    <td class="border" style="width: 50.3px; height: {{ $row_height }};">&nbsp;
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br><br><br><br><br>
                        <table style="height: 246px; width: 100%;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr style="height: 15px;">
                                    <td
                                        style="border-top: 1pt solid black;width: 30%; height: 14px; text-align: center;">
                                        wasit
                                    </td>
                                    <td style="width: 150px; height: 14px; text-align: center;"> </td>
                                    <td
                                        style="border-top: 1pt solid black;width: 30%; height: 14px; text-align: center;">
                                        peserta</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
    <br>
    <table style="height: 246px; width: 100%;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td style="width: 23.1329%; height: 15px;">
                    <img style="display: block;" src="{{ $qr }}" alt="" width="90"
                        height="90" />
                </td>
            </tr>
        </tbody>
    </table>
    {{-- <pagebreak /> --}}

</body>

</html>
