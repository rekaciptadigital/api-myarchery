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
        <table style="width: 100%; height: 40px; font-size: 14pt" border="0">
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
                            UPP
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <br>
        <h1 style="text-align: center">Day {{ $day }}</h1>
        @foreach ($data_report as $item)
            @if ($item['team'] == 'individual' && $item['type'] == 'qualification')
                <h2 style="text-align:center">
                    {{ $item['data'][0][0]['category'] }}
                </h2>
                <h2 style="text-align: center;">Qualification</h2>
                <table class="table"
                    style="width:100%;border: 1px solid black; border-collapse: collapse;font-size: 12pt">
                    <tbody>
                        <tr style="border: 1px solid black;">
                            <th
                                style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                <strong>Medal</strong>
                            </th>
                            <th
                                style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px; width:25%;">
                                <strong>Athlete</strong>
                            </th>
                            <th
                                style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px; width:25%;">
                                <strong>
                                    {{ $item['parent_classification_member_title'] }}
                                </strong>
                            </th>
                            @for ($s = 1; $s <= $item['data'][0][0]['count_session']; $s++)
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Sesi {{ $s }}</strong>
                                </th>
                            @endfor
                            <th
                                style="text-align: center; border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                <strong>Total</strong>
                            </th>
                            <th
                                style="text-align: center; border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                <strong>X+10</strong>
                            </th>
                            <th
                                style="text-align: center; border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                <strong>X</strong>
                            </th>
                        </tr>
                        @foreach ($item['data'][0] as $key2 => $data)
                            <tr style="border: 1px solid black;">
                                <!-- start initiate medals -->
                                @if ($key2 == 0)
                                    @if ($data['medal'] == 'Gold')
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            {{ $data['medal'] }}
                                        </td>
                                    @else
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Gold</td>
                                    @endif
                                @endif

                                @if ($key2 == 1)
                                    @if ($data['medal'] == 'Silver')
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            {{ $data['medal'] }}
                                        </td>
                                    @else
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Silver</td>
                                    @endif
                                @endif

                                @if ($key2 == 2)
                                    @if ($data['medal'] == 'Bronze')
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            {{ $data['medal'] }}
                                        </td>
                                    @else
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            -</td>
                                    @endif
                                @endif
                                <!-- end medals -->
                                <td
                                    style="text-align: left;border: 1px solid black; padding-top:5px; padding-bottom:5px; padding-left:10px;">
                                    {{ $data['athlete'] ? ucwords(strtolower($data['athlete'])) : '-' }}
                                </td>
                                <td
                                    style="text-align: left;border: 1px solid black; padding-top:5px; padding-bottom:5px; padding-left:10px;">
                                    @if ($data['parent_classification_type'] == 2)
                                        {{ ucwords(strtolower($data['country_name'])) }}
                                    @elseif ($data['parent_classification_type'] == 3)
                                        {{ ucwords(strtolower($data['province_name'])) }}
                                    @elseif ($data['parent_classification_type'] == 4)
                                        {{ ucwords(strtolower($data['city_name'])) }}
                                    @elseif ($data['parent_classification_type'] > 5)
                                        {{ ucwords(strtolower($data['children_classification_members_name'])) }}
                                    @else
                                        {{ $data['club_name'] }}
                                    @endif
                                </td>
                                @for ($s = 1; $s <= $item['data'][0][0]['count_session']; $s++)
                                    <td
                                        style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                        {{ $data['scoring']['sessions'][$s] && $data['scoring']['sessions'][$s]['total'] > 0 ? $data['scoring']['sessions'][$s]['total'] : '-' }}
                                    </td>
                                @endfor

                                <td
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    {{ $data['scoring'] ? $data['scoring']['total'] : '-' }}
                                </td>
                                <td
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    {{ $data['scoring'] ? $data['scoring']['total_x_plus_ten'] : '-' }}
                                </td>
                                <td
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    {{ $data['scoring'] ? $data['scoring']['total_x'] : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($item['team'] == 'team' && $item['type'] == 'qualification')
                @if ($item['data'] != [])
                    <h2 style="text-align: center;">
                        {{ $item['category_label'] }}
                    </h2>
                    <h2 style="text-align: center;">Qualification</h2>
                    <table class="table"
                        style="width:100%;border: 1px solid black; border-collapse: collapse; font-size:12pt;">
                        <tbody>
                            <tr style="border: 1px solid black;">
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                    <strong>Medal</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px;padding-bottom:5px; width:30%;">
                                    <strong>Athlete</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px;padding-bottom:5px; width:30%;">
                                    <strong>Nama Tim</strong>
                                </th>
                                <th
                                    style="text-align: center; border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                    <strong>Total</strong>
                                </th>
                                <th
                                    style="text-align: center; border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                    <strong>X+10</strong>
                                </th>
                                <th
                                    style="text-align: center; border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                    <strong>X</strong>
                                </th>
                            </tr>

                            @foreach ($item['data'] as $key => $data)
                                <tr style="border: 1px solid black;">
                                    <!-- start initiate medals -->
                                    @if ($key == 0)
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Gold</td>
                                    @elseif ($key == 1)
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Silver</td>
                                    @else
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Bronze </td>
                                    @endif
                                    <!-- end medals -->
                                    <td
                                        style="text-align: left;border: 1px solid black; padding-top:5px; padding-bottom:5px; padding-left:10px;">
                                        @if (sizeof($data['teams']) > 0)
                                            @foreach ($data['teams'] as $key => $team)
                                                {{ ucwords(strtolower($team['name'])) }} <br>
                                            @endforeach
                                        @else
                                            Belum ada anggota
                                        @endif
                                    </td>
                                    <td
                                        style="text-align: left;border: 1px solid black; padding-left:10px; padding-top:5px; padding-bottom:5px;">
                                        {{ $data['team'] ? $data['team'] : '-' }}
                                    </td>
                                    <td
                                        style="text-align: center;border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                        {{ $data['total'] }}
                                    </td>
                                    <td
                                        style="text-align: center;border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                        {{ $data['total_x_plus_ten'] }}
                                    </td>
                                    <td
                                        style="text-align: center;border: 1px solid black; padding-top:5px;padding-bottom:5px;">
                                        {{ $data['total_x'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            @if ($item['type'] == 'elimination' && $item['team'] == 'individual')
                @if ($item['data'][0] != [])
                    <h2 style="text-align: center;">Elimination</h2>
                    <table class="table"
                        style="width:100%;border: 1px solid black; border-collapse: collapse; font-size:12pt;">
                        <tbody>
                            <tr style="border: 1px solid black;">
                                <th style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px; font-size:16pt;"
                                    colspan="5">
                                    <strong>Medalist by Event</strong>
                                </th>
                            </tr>
                            <tr style="border: 1px solid black;">
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Category</strong>
                                </th>
                                <th
                                    style="text-align: center; border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Date</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Medal</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px; width:25%;">
                                    <strong>Athlete</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px; width:25%;">
                                    <strong>
                                        {{ $item['parent_classification_member_title'] }}
                                    </strong>
                                </th>
                            </tr>
                            @php
                                $rowid = 0;
                                $rowspan = 0;
                            @endphp
                            @foreach ($item['data'][0] as $key => $data)
                                @php
                                    $rowid += 1;
                                @endphp
                                <tr style="border: 1px solid black;">
                                    @if ($key == 0 || $rowspan == $rowid)
                                        @php
                                            $rowid = 0;
                                            $rowspan = count($item['data'][0]);
                                        @endphp
                                        <td style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;"
                                            rowspan="{{ $rowspan }}">
                                            {{ $data['category'] ? $data['category'] : '-' }}</td>
                                        <td style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;"
                                            rowspan="{{ $rowspan }}">
                                            {{ $data['date'] ? $data['date'] : '-' }}</td>
                                    @endif
                                    <td
                                        style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                        {{ $data['medal'] }} </td>
                                    <!-- start initiate medals -->

                                    <!-- end medals -->
                                    <td
                                        style="text-align: left;border: 1px solid black; padding-top:5px; padding-bottom:5px; padding-left:10px;">
                                        {{ $data['athlete'] ? ucwords(strtolower($data['athlete'])) : '-' }}</td>
                                    <td
                                        style="text-align: left;border: 1px solid black; padding-top:5px; padding-bottom:5px;padding-left:10px;">
                                        @if ($data['parent_classification_type'] == 2)
                                            {{ ucwords(strtolower($data['country_name'])) }}
                                        @elseif ($data['parent_classification_type'] == 3)
                                            {{ ucwords(strtolower($data['province_name'])) }}
                                        @elseif ($data['parent_classification_type'] == 4)
                                            {{ ucwords(strtolower($data['city_name'])) }}
                                        @elseif ($data['parent_classification_type'] > 5)
                                            {{ ucwords(strtolower($data['children_classification_members_name'])) }}
                                        @else
                                            {{ ucwords(strtolower($data['club_name'])) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            @if ($item['type'] == 'elimination' && $item['team'] == 'team')
                @if ($item['data'] != [])
                    <h2 style="text-align: center;">
                        {{ $item['category_label'] }}
                    </h2>
                    <h2 style="text-align: center;">Elimination</h2>
                    <table class="table"
                        style="width:100%;border: 1px solid black; border-collapse: collapse; font-size:12pt;">
                        <tbody>
                            <tr style="border: 1px solid black;">
                                <th style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;"
                                    colspan="5">
                                    <strong>Medalist by Event</strong>
                                </th>
                            </tr>
                            <tr style="border: 1px solid black;">
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Category</strong>
                                </th>
                                <th
                                    style="text-align: center; border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Date</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Medal</strong>
                                </th>
                                <th
                                    style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                    <strong>Nama Tim</strong>
                                </th>
                            </tr>
                            @php
                                $rowid = 0;
                                $rowspan = 0;
                            @endphp
                            @foreach ($item['data']->take(3) as $key => $data)
                                @php
                                    $rowid += 1;
                                @endphp
                                <tr style="border: 1px solid black;">
                                    @if ($key == 0 || $rowspan == $rowid)
                                        @php
                                            $rowid = 0;
                                            $rowspan = count($item['data']);
                                        @endphp
                                        <td style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;"
                                            rowspan="{{ $rowspan }}">
                                            {{ $data['category'] ? $data['category'] : '-' }}</td>
                                        <td style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;"
                                            rowspan="{{ $rowspan }}">
                                            {{ $data['date'] ? $data['date'] : '-' }}</td>
                                    @endif
                                    <!-- start initiate medals -->
                                    @if ($data['elimination_ranked'] == '1')
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Gold</td>
                                    @elseif ($data['elimination_ranked'] == '2')
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Silver</td>
                                    @elseif ($data['elimination_ranked'] == '3')
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Bronze </td>
                                    @else
                                        <td
                                            style="text-align: center;border: 1px solid black; padding-top:5px; padding-bottom:5px;">
                                            Bronze</td>
                                    @endif
                                    <!-- end medals -->
                                    <td
                                        style="text-align: left;border: 1px solid black; padding-top:5px; padding-bottom:5px; padding-left:10px;">
                                        {{ $data['team_name'] ? $data['team_name'] : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
            <br>
        @endforeach
    </div>
</body>

</html>
