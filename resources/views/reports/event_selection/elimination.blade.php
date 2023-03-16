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
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        {!! $logo_archery !!}
                    </td>
                    <td style="width: 42%; height: 30px; ">
                        <p style="text-align: left; font-size: 14pt; font-family: helvetica;">
                            <strong><span style="font-size: 20px;">{{ $event_name_report }}</span></strong> <br />
                            {{ $event_location_report }}<br />
                            {{ $event_date_report }}
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2 style="text-align: center; font-size: 24px;">Elimination</h2>
        @foreach ($datas as $key => $elimination)
            <p style="font-size: 18px; text-align: right; margin-bottom: 5px;"><b>After
                    {{ $elimination['total_arrow'] }} arrows</b>
            </p>
            <table class="table"
                style="width:100%;border: 1px solid black; border-collapse: collapse; margin-bottom: 25px;">
                <thead>
                    <tr>
                        <th style="text-align: center;border: 1px solid black; font-size: 20px; background-color: lightgray;"
                            colspan="{{ $elimination['session_in_elimination_selection'] + 6 }}">
                            {{ $elimination['category'] }}
                        </th>
                    </tr>
                </thead>
                <tbody style="font-size: 18px;">
                    <tr style="border: 1px solid black; background-color: lightgray;">
                        <th style="text-align: center;border: 1px solid black; ">
                            <strong>Pos.</strong>
                        </th>
                        <th style="text-align: center;border: 1px solid black; width:1% ">
                            <strong>Budrest</strong>
                        </th>
                        <th style="text-align: center;border: 1px solid black; width:5% ">
                            <strong>Athlete</strong>
                        </th>
                        <th style="text-align: center;border: 1px solid black;width:5%">
                            <strong>Club</strong>
                        </th>
                        @for ($i = 1; $i <= $elimination['session_in_elimination_selection']; $i++)
                            <th style="text-align: center;border: 1px solid black;">
                                <strong>Eli-{{ $i }}</strong>
                            </th>
                        @endfor
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>Total</strong>
                        </th>
                        <th style="text-align: center; border: 1px solid black;">
                            <strong>IRAT</strong>
                        </th>
                    </tr>
                    @php($i = 1)
                    @foreach ($elimination['data'] as $key => $data)
                        <tr style="border: 1px solid black;">
                            <td style="text-align: center;border: 1px solid black;"> {{ $i }}</td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['member']['bud_rest_number'] }} {{ $data['member']['target_face'] }} </td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['member'] ? strtoupper($data['member']['name']) : '-' }}</td>
                            <td style="text-align: center;border: 1px solid black;">
                                {{ $data['club_name'] ? strtoupper($data['club_name']) : '-' }}</td>
                            @for ($j = 1; $j <= $elimination['session_in_elimination_selection']; $j++)
                                <td style="text-align: center;border: 1px solid black;">
                                    {{ isset($data['sessions']) && isset($data['sessions'][$i]) && isset($data['sessions'][$i]['total']) ? $data['sessions'][$i]['total'] : 0 }}
                                </td>
                            @endfor
                            <td style="text-align: center;border: 1px solid black;"> {{ $data['total'] }}</td>
                            <td style="text-align: center;border: 1px solid black;"> {{ $data['total_irat'] }}</td>
                        </tr>
                        @php($i++)
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>
</body>

</html>
