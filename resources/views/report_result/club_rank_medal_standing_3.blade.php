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
    </style>
</head>

<body>
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
                            Standing 3<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <h1 style="text-align: center">Medals Standing 3</h1>
        <table class="table" style="width:100%; border-collapse: collapse; font-size: 18pt;" border="1">
            <tbody>
                <tr>
                    <th rowspan="3" style="text-align: center; padding:5px;">
                        <strong>NO</strong>
                    </th>
                    <th rowspan="3" style="text-align: center; padding:5px;">
                        <strong>
                            {{ $parent_classification_member_title }}
                        </strong>
                    </th>
                    @foreach ($title_header as $key => $value)
                        <th colspan="6" style="text-align: center; padding:5px;">
                            <strong>{{ $key }}</strong>
                        </th>
                    @endforeach
                    <th rowspan="2" colspan="3" style="text-align: center; padding:5px;">
                        <strong>TOTAL</strong>
                    </th>
                </tr>
                <tr>
                    @foreach ($title_header as $key2 => $value2)
                        <th colspan="3" style="text-align:center; padding:5px">Kualifikasi</th>
                        <th colspan="3" style="text-align:center; padding:5px">Eliminasi</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($title_header as $key => $value2)
                        <th style="text-align:center; padding:5px;">G</th>
                        <th style="text-align:center; padding:5px;">S</th>
                        <th style="text-align:center; padding:5px;">B</th>

                        <th style="text-align:center; padding:5px;">G</th>
                        <th style="text-align:center; padding:5px;">S</th>
                        <th style="text-align:center; padding:5px;">B</th>
                    @endforeach
                    <th style="text-align:center; padding:5px;">G</th>
                    <th style="text-align:center; padding:5px;">S</th>
                    <th style="text-align:center; padding:5px;">B</th>
                </tr>

                @php($i = 1)
                @foreach ($datatables as $key => $data)
                    <tr>
                        <td style="text-align:center; padding:5px;">{{ $i }}</td>
                        <td style="padding-left:15px;padding-top:5px;padding-bottom:5px">
                            {{ $data['contingent_name'] }}
                        </td>

                        @foreach ($title_header as $key => $value)
                            <td style="text-align:center; padding:5px;">
                                {{ $data[$key]['qualification']['gold'] > 0 ? $data[$key]['qualification']['gold'] : '-' }}
                            </td>
                            <td style="text-align:center; padding:5px;">
                                {{ $data[$key]['qualification']['silver'] > 0 ? $data[$key]['qualification']['silver'] : '-' }}
                            </td>
                            <td style="text-align:center; padding:5px;">
                                {{ $data[$key]['qualification']['bronze'] > 0 ? $data[$key]['qualification']['bronze'] : '-' }}
                            </td>
                            <td style="text-align:center; padding:5px;">
                                {{ $data[$key]['elimination']['gold'] > 0 ? $data[$key]['elimination']['gold'] : '-' }}
                            </td>
                            <td style="text-align:center; padding:5px;">
                                {{ $data[$key]['elimination']['silver'] > 0 ? $data[$key]['elimination']['silver'] : '-' }}
                            </td>
                            <td style="text-align:center; padding:5px;">
                                {{ $data[$key]['elimination']['bronze'] > 0 ? $data[$key]['elimination']['bronze'] : '-' }}
                            </td>
                        @endforeach

                        <td style="text-align:center; padding:5px;">{{ $data['total_all_gold'] }}</td>
                        <td style="text-align:center; padding:5px;">{{ $data['total_all_silver'] }}</td>
                        <td style="text-align:center; padding:5px;">{{ $data['total_all_bronze'] }}</td>
                    </tr>
                    @php($i++)
                @endforeach

                <tr>
                    <td colspan="2" style="text-align:center;padding:5px"><strong>JUMLAH</strong></td>
                    @foreach ($detail_total_medal_for_last_row as $key_detail_total_medal_for_last_row => $value_detail_total_medal_for_last_row)
                        @foreach ($value_detail_total_medal_for_last_row as $key_value_detail_total_medal_for_last_row => $value_value_detail_total_medal_for_last_row)
                            @foreach ($value_value_detail_total_medal_for_last_row as $key_value_value_detail_total_medal_for_last_row => $value_value_value_detail_total_medal_for_last_row)
                                <td style="text-align:center; padding:5px;">
                                    <strong>{{ $value_value_value_detail_total_medal_for_last_row }}</strong>
                                </td>
                            @endforeach
                        @endforeach
                    @endforeach
                    @foreach ($detail_sum_medal_last_row as $value_detail_sum_medal_last_row)
                        <td style="text-align:center; padding:5px;">
                            <strong>{{ $value_detail_sum_medal_last_row }}</strong>
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
