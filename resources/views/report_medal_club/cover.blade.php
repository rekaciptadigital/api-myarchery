<!DOCTYPE html>
<html>

<head>
    <title>Page Title</title>
    <style type="text/css">
        div.page {
            page-break-after: always;
            page-break-inside: avoid;
            break-after: page;
            float: none;
            overflow: visible;
        }

        div.box-bottom {
            background-color: #E7EDF6;
            padding: 40px;
            margin: 10px;
        }
    </style>
</head>

<body>
    <div style="margin-bottom: 150px;">
        <br>
        <h1 style="text-align: left; font-size: 50pt; font-family: helvetica;">LAPORAN</h1>
        <h2 style="text-align: left; font-size: 30pt; font-family: helvetica;">PEROLEHAN MEDALI</h1>
            <hr style="border: 10px solid #E7EDF6; width: 40%; margin-left: 0;">
            </hr>
    </div>
    <div>
        <img src="{{ $cover_event }}" alt="" srcset="" width="25%">
    </div>
    <div style="margin-bottom: 300px;"></div>
    <div class="box-bottom">
        <strong
            style="text-align: left; font-size: 25pt; font-family: helvetica;">{!! $event_name_report !!}</strong><br></br>
        <hr style="height:3px;border:none;color:black;background-color:black;">
        <table style="width: 100%; height: 40px;" border="0">
            <tbody>
                <tr style="height: 20px;">
                    <td style="width: 75%; height: 20px; ">
                        <p style="text-align: left; font-size: 20pt; font-family: helvetica;">
                            {!! $event_location_report !!}<br><br />
                            {!! $event_date_report !!}
                        </p>
                    </td>
                    <td style="width: 10%;"></td>
                    <td style="width: 30%;">{!! $logo_archery !!}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>

</html>
