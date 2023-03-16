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
        <table style="width: 50%; height: 20%;" border="0">
            <tbody>
                <tr style="height: 20px;">
                    <td style="width: 2%; height: 50px;" rowspan="2"></td>
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        <img src="{{ $logo_event }}" alt="" width="70%">
                    </td>
                    <td style="width: 10%; height: 50px;" rowspan="2">{!! $logo_archery !!}</td>
                    <td style="width: 42%; height: 30px; ">
                        <p style="text-align: left; font-size: 14pt; font-family: helvetica;">
                            <strong>
                                <span style="font-size: 20px;">
                                    {{ $event_name_report }}
                                </span>
                            </strong> <br />
                            {{ $event_location_report }}<br />
                            {{ $event_date_report }}
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2 style="text-align: center; font-size: 24px;">Hasil Akhir</h2>
        @foreach ($datas as $key => $all_results)
            <p style="font-size: 18px; text-align: right; margin-bottom: 5px;"><b>After Total IRAT</b></p>
            <table class="table"
                style="width:100%;border: 1px solid black; border-collapse: collapse; margin-bottom: 25px;">
                <thead>
                    <tr>
                        <th style="text-align: center;border: 1px solid black; font-size: 20px; background-color: lightgray;"
                            colspan="16">{{ $all_results['category'] }}
                        </th>
                    </tr>
                </thead>
                <tbody style="font-size: 18px;">
                    <tr style="border: 1px solid black; background-color: lightgray;">
                        <th style="text-align: center;border: 1px solid black; ">
                            <strong>Pos.</strong>
                        </th>
                        <th style="text-align: center;border: 1px solid black; ">
                            <strong>Budrest</strong>
                        </th>
                        <th style="text-align: center;border: 1px solid black; ">
                            <strong>Athlete</strong>
                        </th>
                        <th style="text-align: center;border: 1px solid black; ">
                            <strong>Club/Contingent</strong>
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>Total Kualifikasi</strong>
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>IRAT Kualifikasi</strong>
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>Total Eliminasi</strong>
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>IRAT Eliminasi</strong>
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>Total IRAT Keseluruhan</strong>
                        </th>
                    </tr>
                    @php($i = 1)
                    @foreach ($all_results['data'] as $key => $data)
                        <tr style="border: 1px solid black;">
                            <td style="text-align: center;border: 1px solid black;"> {{ $i }}</td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['member']['bud_rest_number'] }} {{ $data['member']['target_face'] }} </td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['member'] ? strtoupper($data['member']['name']) : '-' }}</td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $with_contingent == 0 ? strtoupper($data['club_name']) : strtoupper($data['city_name']) }}
                            </td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['qualification']['total'] }}</td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['qualification']['total_irat'] }}
                            </td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['elimination']['total'] }}
                            </td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['elimination']['total_irat'] }}
                            </td>
                            <td style="text-align: center;border: 1px solid black;"> {{ $data['all_total_irat'] }}</td>
                        </tr>
                        @php($i++)
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>
</body>

</html>
