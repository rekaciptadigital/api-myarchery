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
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        <img src="{{ $logo_event }}" alt="" srcset="" width="80%">
                    </td>
                    <td style="width: 10%; height: 50px;" rowspan="2">{!! $logo_archery !!}</td>
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 42%; height: 50px; ">
                        <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                            <strong>
                                <span style="font-size: 30px;">
                                    {{ $event_name_report }}
                                </span>
                            </strong> <br /><br />
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
                            Detail<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <br>

        <h1 style="text-align: center">Rank {{ $rank }}</h1>
        <h2 style="text-align: center">
            @if ($parent_classification_type == 2)
                {{ ucwords(strtolower($country_name)) }}
            @elseif ($parent_classification_type == 3)
                {{ ucwords(strtolower($province_name)) }}
            @elseif ($parent_classification_type == 4)
                {{ ucwords(strtolower($city_name)) }}
            @elseif ($parent_classification_type > 5)
                {{ ucwords(strtolower($children_classification_members_name)) }}
            @else
                {{ $club_name }}
            @endif
        </h2>

        <table style="width:100%;border: 1px solid black; border-collapse: collapse; font-size: 12pt;">
            <thead>
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black; font-size:16pt" colspan="5">
                        <strong>Medalist by {{ $parent_classification_member_title }}</strong>
                    </th>
                </tr>
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>{{ $parent_classification_member_title }}</strong>
                    </th>
                    <th style="text-align: center; border: 1px solid black; font-size:14pt;">
                        <strong>Gold</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>Silver</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>Bronze</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr style="border: 1px solid black;">
                    <td style="text-align: center;border: 1px solid black;">
                        @if ($parent_classification_type == 2)
                            {{ ucwords(strtolower($country_name)) }}
                        @elseif ($parent_classification_type == 3)
                            {{ ucwords(strtolower($province_name)) }}
                        @elseif ($parent_classification_type == 4)
                            {{ ucwords(strtolower($city_name)) }}
                        @elseif ($parent_classification_type > 5)
                            {{ ucwords(strtolower($children_classification_members_name)) }}
                        @else
                            {{ $club_name }}
                        @endif
                    </td>
                    <td style="text-align: center;border: 1px solid black;">{{ $total_gold }}</td>
                    <td style="text-align: center;border: 1px solid black;">{{ $total_silver }}</td>
                    <td style="text-align: center;border: 1px solid black;">{{ $total_bronze }}</td>
                </tr>
            </tbody>
        </table>

        <br>
        <h2 style="text-align: center">Detail Medal</h2>
        <table style="width:100%;border: 1px solid black; border-collapse: collapse;font-size: 12pt;">
            <thead>
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black; font-size:16pt;" colspan="5">
                        <strong>Medalist by {{ $parent_classification_member_title }}</strong>
                    </th>
                </tr>
                <tr style="border: 1px solid black;">
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>Competition Type</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>Class</strong>
                    </th>
                    <th style="text-align: center; border: 1px solid black; font-size:14pt;">
                        <strong>Gold</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>Silver</strong>
                    </th>
                    <th style="text-align: center;border: 1px solid black; font-size:14pt;">
                        <strong>Bronze</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($category as $key => $c)
                    @php
                        $index = 1;
                    @endphp
                    @foreach ($c['age_category'] as $key2 => $c2)
                        @if ($index == 1)
                            <tr>
                                @if ($c['1']['count_rowspan'] > 1)
                                    <th rowspan="{{ $c['1']['count_rowspan'] }}"
                                        style="text-align: center;border: 1px solid black; ">
                                        {{ $key }}
                                    </th>
                                    <td style="text-align: center;border: 1px solid black; ">{{ $key2 }}</td>
                                    <td style="text-align: center;border: 1px solid black; ">
                                        {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['gold'] > 0 ? $dms['category'][$key]['age_category'][$key2]['gold'] : '-' }}
                                    </td>
                                    <td style="text-align: center;border: 1px solid black; ">
                                        {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['silver'] > 0 ? $dms['category'][$key]['age_category'][$key2]['silver'] : '-' }}
                                    </td>
                                    <td style="text-align: center;border: 1px solid black; ">
                                        {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['bronze'] > 0 ? $dms['category'][$key]['age_category'][$key2]['bronze'] : '-' }}
                                    </td>
                                @else
                                    <th style="text-align: center;border: 1px solid black; ">
                                        {{ $key }}
                                    </th>
                                    <td style="text-align: center;border: 1px solid black; ">{{ $key2 }}</td>
                                    <td style="text-align: center;border: 1px solid black; ">
                                        {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['gold'] > 0 ? $dms['category'][$key]['age_category'][$key2]['gold'] : '-' }}
                                    </td>
                                    <td style="text-align: center;border: 1px solid black; ">
                                        {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['silver'] > 0 ? $dms['category'][$key]['age_category'][$key2]['silver'] : '-' }}
                                    </td>
                                    <td style="text-align: center;border: 1px solid black; ">
                                        {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['bronze'] > 0 ? $dms['category'][$key]['age_category'][$key2]['bronze'] : '-' }}
                                    </td>
                                @endif
                            </tr>
                        @else
                            <tr>
                                <td style="text-align: center;border: 1px solid black; ">{{ $key2 }}</td>
                                <td style="text-align: center;border: 1px solid black; ">
                                    {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['gold'] > 0 ? $dms['category'][$key]['age_category'][$key2]['gold'] : '-' }}
                                </td>
                                <td style="text-align: center;border: 1px solid black; ">
                                    {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['silver'] > 0 ? $dms['category'][$key]['age_category'][$key2]['silver'] : '-' }}
                                </td>
                                <td style="text-align: center;border: 1px solid black; ">
                                    {{ isset($dms['category'][$key]['age_category'][$key2]) && $dms['category'][$key]['age_category'][$key2]['bronze'] > 0 ? $dms['category'][$key]['age_category'][$key2]['bronze'] : '-' }}
                                </td>
                            </tr>
                        @endif
                        @php
                            $index++;
                        @endphp
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
