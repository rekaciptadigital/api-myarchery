<!DOCTYPE html>
<html>

<head>
    <title>Page Title</title>
    <style type="text/css">
        .vl {
            border-left: 2px solid black;
            height: 150px;
        }

        div.page {
            page-break-after: always;
            page-break-inside: avoid;
            break-after: page;
            float: none;
            overflow: visible;
        }

        div.page.table {
            font-size: 22pt;
        }

        * {
            font-family: helvetica;
        }
    </style>
</head>

<body translate="no">
    <div class="page" style="break-after:page">
        <table style="width: 100%; height: 40px;" border="0">
            <tbody>
                <tr style="height: 40px;">
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 10%; height: 50px;" rowspan="2"><img src="{{ $logo_event }}" alt=""
                            srcset="" width="80%"></td>
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        <img src="https://api.myarchery.id/new-logo-archery.png" alt="" width="80%"></img>'
                    </td>
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 42%; height: 50px; ">
                        <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                            <strong><span style="font-size: 30px;">{{ $event_name_report }}</span></strong> <br /><br />
                            {{ $event_location_report }}<br />
                            {{ $event_date_report }}
                        </p>
                    </td>
                    <td style="width: 2%; height: 50px;" rowspan="2">
                        <div class="vl"></div>
                    </td>
                    <td style="width: 10%; height: 50px; ">
                        <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                            Medals<br />
                            Standing<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <h1 style="text-align: center">Medals Standing</h1>
        <table class="table" style="width:100%; border-collapse: collapse; font-size: 12pt;" border="1">
            <tbody>
                <tr>
                    <th rowspan="3" style="text-align: center; padding:5px;"><strong>NO</strong></th>
                    <th rowspan="3" style="text-align: center; padding:5px;">
                        <strong>
                            {{ $parent_classification_member_title }}
                        </strong>
                    </th>
                    @foreach ($headers as $key => $value)
                        <th colspan="{{ $value[0]['count_colspan'] }}" style="text-align: center; padding:5px;">
                            <strong>{{ $key }}</strong>
                        </th>
                    @endforeach
                    <th rowspan="2" colspan="3" style="text-align: center; padding:5px;">
                        <strong>TOTAL</strong>
                    </th>
                </tr>
                <tr>
                    @foreach ($headers as $key2 => $value2)
                        @foreach ($value2['age_category'] as $key3 => $value3)
                            <th colspan="3" style="text-align:center; padding:5px">{{ $key3 }}</th>
                        @endforeach
                    @endforeach
                </tr>
                <tr>
                    @foreach ($headers as $key => $value2)
                        @foreach ($value2['age_category'] as $key => $value3)
                            <th style="text-align:center; padding:5px; background: #e7ac54; width:20px;">E</th>
                            <th style="text-align:center; padding:5px; width:20px;">P</th>
                            <th style="text-align:center; padding:5px; background: #b78458;width:20px;">PR</th>
                        @endforeach
                    @endforeach
                    <th style="text-align:center; padding:5px; background: #e7ac54; width:20px;">E</th>
                    <th style="text-align:center; padding:5px; width:20px;">P</th>
                    <th style="text-align:center; padding:5px; background: #b78458; width:20px;">PR</th>
                </tr>

                @php($i = 1)
                @foreach ($datatables as $key => $data)
                    <tr>
                        <td style="text-align:center; padding:5px;">{{ $i }}</td>
                        <td style="padding-left:15px;padding-top:5px;padding-bottom:5px">
                            @if ($data['parent_classification_type'] == 2)
                                {{ $data['country_name'] }}
                            @elseif ($data['parent_classification_type'] == 3)
                                {{ $data['province_name'] }}
                            @elseif ($data['parent_classification_type'] == 4)
                                {{ $data['city_name'] }}
                            @elseif($data['parent_classification_type'] > 5)
                                {{ $data['children_classification_members_name'] }}
                            @else
                                {{ $data['club_name'] }}
                            @endif
                        </td>
                        @foreach ($data['medal_array'] as $item)
                            <td style="text-align:center; padding:5px;">{{ $item === 0 ? '' : $item }}</td>
                        @endforeach
                        <td style="text-align:center; padding:5px;">{{ $data['total_gold'] }}</td>
                        <td style="text-align:center; padding:5px;">{{ $data['total_silver'] }}</td>
                        <td style="text-align:center; padding:5px;">{{ $data['total_bronze'] }}</td>
                    </tr>
                    @php($i++)
                @endforeach

                <tr>
                    <td colspan="2" style="text-align:center;padding:5px"><strong>JUMLAH</strong></td>
                    @foreach ($total_medal_by_category as $value_medal_each_category)
                        <td style="text-align:center; padding:5px;"><strong>{{ $value_medal_each_category }}</strong>
                        </td>
                    @endforeach
                    @foreach ($total_medal_by_category_all_club as $value_medal_total)
                        <td style="text-align:center; padding:5px;"><strong>{{ $value_medal_total }}</strong></td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>