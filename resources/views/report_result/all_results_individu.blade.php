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
                            Qualification<br />
                            Round<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <br>

        <p style="text-align: center; font-size: 30px;"><strong>{{ $category }}</strong></p>
        <h2 style="text-align: center">Qualification Report</h2>

        <table style="width:100%;border: 1px solid black; border-collapse: collapse;">
            <thead>
                <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
            </thead>
            <tbody style="font-size: 24px;">

                <tr style="border: 1px solid black;">

                    <th style="text-align: center; border: 1px solid black;"><strong>RANK</strong></th>
                    <th style="text-align: center; border: 1px solid black;"><strong>NAME</strong></th>
                    <th style="text-align: center;border: 1px solid black; "><strong>CLUB</strong></th>
                    <th style="text-align: center;border: 1px solid black; "><strong>SESI 1</strong></th>
                    <th style="text-align: center;border: 1px solid black; "><strong>SESI 2</strong></th>
                    <th style="text-align: center;border: 1px solid black; "><strong>TOTAL</strong></th>
                    <th style="text-align: center;border: 1px solid black; "><strong>X</strong></th>
                    <th style="text-align: center;border: 1px solid black; "><strong>X+10</strong></th>

                </tr>
                @php $i = 1 @endphp
                @foreach ($data_report as $data)
                    <tr style="border: 1px solid black;">

                        <td style="text-align: center;border: 1px solid black;">{{ $i++ }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['member'] ? $data['member']['name'] : '-' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['club_name'] ? $data['club_name'] : '-' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['sessions']['1'] ? $data['sessions']['1']['total'] : '-' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ isset($data['sessions']['2']) ? $data['sessions']['2']['total'] : '-' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['total'] ? $data['total'] : '0' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['total_x'] ? $data['total_x'] : '0' }}</td>
                        <td style="text-align: center;border: 1px solid black;">
                            {{ $data['total_x_plus_ten'] ? $data['total_x_plus_ten'] : '0' }}</td>


                    </tr>
                @endforeach
                <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
            </tbody>
        </table>
    </div>
</body>

</html>
