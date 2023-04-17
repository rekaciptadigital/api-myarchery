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
                            Standing<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <h1 style="text-align: center; font-size:30pt;">Medals Standing 2</h1>
        <table class="table" style="width:100%; border-collapse: collapse; font-size: 18pt;" border="1">
            <tbody>
                <tr>
                    <th rowspan="2" style="text-align: center;padding:5px"><strong>NO</strong></th>
                    <th rowspan="2" style="text-align: center;padding:5px">
                        <strong>
                            {{ $parent_classification_member_title }}
                        </strong>
                    </th>
                    <th colspan="4" style="text-align: center; padding:5px">
                        <strong>Individual</strong>
                    </th>
                    <th colspan="4" style="text-align: center;padding:5px">
                        <strong>Team</strong>
                    </th>
                    <th colspan="4" style="text-align: center;padding:5px"><strong>Total</strong></th>
                </tr>

                <tr>
                    <th style="text-align:center;padding:5px">G</th>
                    <th style="text-align:center;padding:5px">S</th>
                    <th style="text-align:center;padding:5px">B</th>
                    <th style="text-align:center;padding:5px">Tot</th>
                    <th style="text-align:center;padding:5px">G</th>
                    <th style="text-align:center;padding:5px">S</th>
                    <th style="text-align:center;padding:5px">B</th>
                    <th style="text-align:center;padding:5px">Tot</th>
                    <th style="text-align:center;padding:5px">G</th>
                    <th style="text-align:center;padding:5px">S</th>
                    <th style="text-align:center;padding:5px">B</th>
                    <th style="text-align:center;padding:5px">Tot</th>
                </tr>


                @php($i = 1)
                @foreach ($datatables as $key => $data)
                    <tr>
                        <td style="text-align:center; padding:5px">{{ $i }}</td>
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
                        <td style="text-align:center; padding:5px">
                            {{ $data['detail_modal_by_group']['indiividu']['gold'] > 0 ? $data['detail_modal_by_group']['indiividu']['gold'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['indiividu']['silver'] > 0 ? $data['detail_modal_by_group']['indiividu']['silver'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['indiividu']['bronze'] ? $data['detail_modal_by_group']['indiividu']['bronze'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['indiividu']['total'] ? $data['detail_modal_by_group']['indiividu']['total'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['team']['gold'] > 0 ? $data['detail_modal_by_group']['team']['gold'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['team']['silver'] > 0 ? $data['detail_modal_by_group']['team']['silver'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['team']['bronze'] > 0 ? $data['detail_modal_by_group']['team']['bronze'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">
                            {{ $data['detail_modal_by_group']['team']['total'] > 0 ? $data['detail_modal_by_group']['team']['total'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">{{ $data['gold'] > 0 ? $data['gold'] : '-' }}</td>
                        <td style="text-align:center;padding:5px">{{ $data['silver'] > 0 ? $data['silver'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">{{ $data['bronze'] > 0 ? $data['bronze'] : '-' }}
                        </td>
                        <td style="text-align:center;padding:5px">{{ $data['total'] > 0 ? $data['total'] : '-' }}</td>
                    </tr>
                    @php($i++)
                @endforeach

                <tr>
                    <td colspan="2" style="text-align:center; padding:5px"><strong>JUMLAH</strong></td>
                    <td style="text-align:center; padding:5px">
                        <strong>{{ $gold_individu }}</strong>
                    </td>
                    <td style="text-align:center; padding:5px">
                        <strong>{{ $silver_individu }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $bronze_individu }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $total_medal_individu }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $gold_team }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $silver_team }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $bronze_team }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $total_medal_team }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $total_gold }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $total_silver }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $total_bronze }}</strong>
                    </td>
                    <td style="text-align:center;padding:5px">
                        <strong>{{ $total_all }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
