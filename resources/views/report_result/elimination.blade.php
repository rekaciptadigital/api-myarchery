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
        <!-- <img src="https://i.postimg.cc/ZRR5vW05/header.png" alt="Trulli" width="100%"> -->
        <table style="width: 100%; height: 40px;" border="0">
            <tbody>
                <tr style="height: 40px;">
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 10%; height: 50px;" rowspan="2"><img src="{{ $logo_event }}" alt=""
                            srcset="" width="80%"></td>
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
        <h1 style="text-align: center">{{ $category }}</h1>
        <h2 style="text-align: center">Elimination</h2>
        <table class="table" style="width:100%;border: 1px solid black; border-collapse: collapse;">
            <thead>
                <!-- <tr><th>Table Heading</th></tr> -->
            </thead>
            <tbody style="font-size: 24px;">
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black; " colspan="5">
                        <strong>Medalist by Event</strong>
                    </th>
                </tr>
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black;">
                        <strong>Category</strong>
                    </th>
                    <th style="text-align: center; border: 1px solid black;">
                        <strong>Date</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; ">
                        <strong>Medal</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; ">
                        <strong>Athlete</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; ">
                        <strong>Club</strong>
                    </th>
                </tr>
                @php
                    $rowid = 0;
                    $rowspan = 0;
                @endphp
                @foreach ($data_report as $key => $data)
                    @php
                        $rowid += 1;
                    @endphp
                    <tr style="border: 1px solid black;">
                        @if ($key == 0 || $rowspan == $rowid)
                            @php
                                $rowid = 0;
                                $rowspan = count($data_report);
                            @endphp
                            <td style="text-align: center;border: 1px solid black;" rowspan="{{ $rowspan }}">
                                {{ $data['category'] ? $data['category'] : '-' }}</td>
                            <td style="text-align: center;border: 1px solid black;" rowspan="{{ $rowspan }}">
                                {{ $data['date'] ? $data['date'] : '-' }}</td>
                        @endif
                        <td style="text-align: left;border: 1px solid black;">{{ $data['medal'] }} </td>
                        <!-- start initiate medals -->
                        <!-- @if ($key == 0)
@if ($data['medal'] == 'Gold')
<td style="text-align: left;border: 1px solid black;">{{ $data['medal'] }} </td>
@else
<td style="text-align: left;border: 1px solid black;">Gold</td>
@endif
@elseif ($key == 1)
@if ($data['medal'] == 'Silver')
<td style="text-align: left;border: 1px solid black;">{{ $data['medal'] }} </td>
@else
<td style="text-align: left;border: 1px solid black;">Silver</td>
@endif
@else
@if ($data['medal'] == 'Bronze')
<td style="text-align: left;border: 1px solid black;">{{ $data['medal'] }}</td>
@else
<td style="text-align: left;border: 1px solid black;">Bronze </td>
@endif
@endif -->
                        <!-- end medals -->
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['athlete'] ? $data['athlete'] : '-' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['club'] ? $data['club'] : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
