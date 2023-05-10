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

        * {
            font-family: helvetica;
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
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        <img src="{{ $logo_event }}" alt="" srcset="" width="80%">
                    </td>
                    <td style="width: 10%; height: 50px;" rowspan="2">{!! $logo_archery !!}</td>
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
                            {{ $competition }}<br />
                            {{ $type }}<br />
                            Round<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <br>
        <p style="text-align: center; font-size: 30px;"><strong>{{ $category }}</strong></p>
        <h2 style="text-align: center">Qualification</h2>
        <table class="table" style="width:100%;border: 1px solid black; border-collapse: collapse; font-size: 14pt;">
            <tbody>
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black; padding:5px;">
                        <strong>Medal</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black;padding:5px;">
                        <strong>Athlete</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black;padding:5px;">
                        <strong>{{ $parent_classification_member_title }}</strong>
                    </th>
                    <th style="text-align: center; border: 1px solid black;padding:5px;">
                        <strong>Total</strong>
                    </th>
                    <th style="text-align: center; border: 1px solid black;padding:5px;">
                        <strong>X+10</strong>
                    </th>
                    <th style="text-align: center; border: 1px solid black;padding:5px;">
                        <strong>X</strong>
                    </th>
                </tr>
                @foreach ($data_report as $key => $data)
                    @if ($key <= 2)
                        @isset($data['teams'])
                            <tr style="border: 1px solid black;">
                                <!-- start initiate medals -->
                                @if ($key == 0)
                                    <td style="text-align: center;border: 1px solid black; padding:5px;">Gold</td>
                                @elseif ($key == 1)
                                    <td style="text-align: center;border: 1px solid black;padding:5px;">Silver</td>
                                @else
                                    <td style="text-align: center;border: 1px solid black;padding:5px;">Bronze </td>
                                @endif
                                <!-- end medals -->
                                <td
                                    style="text-align: left;border: 1px solid black;padding-left:10px;padding-top:5px;padding-bottom:5px;">
                                    @if (sizeof($data['teams']) > 0)
                                        @foreach ($data['teams'] as $key => $team)
                                            {{ $team['name'] }} <br>
                                        @endforeach
                                    @else
                                        Belum ada anggota
                                    @endif
                                </td>
                                <td
                                    style="text-align: left;border: 1px solid black;padding-left:10px;padding-top:5px;padding-bottom:5px;">
                                    @if ($data['parent_classification_type'] == 2)
                                        {{ ucwords(strtolower($data['country_name'])) }}
                                    @elseif ($data['parent_classification_type'] == 3)
                                        {{ ucwords(strtolower($data['province_name'])) }}
                                    @elseif ($data['parent_classification_type'] == 4)
                                        {{ ucwords(strtolower($data['city_name'])) }}
                                    @elseif ($data['parent_classification_type'] == 6)
                                        {{ ucwords(strtolower($data['children_classification_members_name'])) }}
                                    @else
                                        {{ $data['club_name'] }}
                                    @endif
                                </td>
                                <td style="text-align: center;border: 1px solid black;padding:5px;">{{ $data['total'] }}
                                </td>
                                <td style="text-align: center;border: 1px solid black;padding:5px;">
                                    {{ $data['total_x_plus_ten'] }}
                                </td>
                                <td style="text-align: center;border: 1px solid black;padding:5px;">
                                    {{ $data['total_x'] }}
                                </td>
                            </tr>
                        @endisset
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
