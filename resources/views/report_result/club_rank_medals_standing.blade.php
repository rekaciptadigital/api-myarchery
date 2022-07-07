<!DOCTYPE html>
<html>
  <head>
    <title>Page Title</title>
    <style type="text/css" >
      .vl {
        border-left: 2px solid black;
        height: 150px;
      }

    div.page
    {
        page-break-after: always;
        page-break-inside: avoid;
        break-after:page;
		float:none;
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
                  <td style="width: 10%; height: 50px;" rowspan="2">{!! $logo_event !!}</td>
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
                          Medal<br />
                          Standing<br />
                      </p>
                  </td>
              </tr>
          </tbody>
      </table>
      <hr style="height:3px;border:none;color:black;background-color:black;" />
      <h1 style="text-align: center">Medal Standing</h1>
      <table  class="table" style="width:100%;border: 1px solid black; border-collapse: collapse; font-size: 10px; transform: rotate(270deg);" border="1">
        <thead></thead>
        <tbody>
            <tr>
                <th rowspan="3" style="text-align: center;"><strong>NO</strong></th>
                <th rowspan="3" style="text-align: center;"><strong>KLUB/KONTINGEN</strong></th>
                <!-- foreach -->
                @foreach ($headers as $key => $value)
                    <th colspan="{{ $value[0]['count_colspan'] }}" style="text-align: center;">
                        <strong>{{ $key }}</strong>
                    </th>
                @endforeach
                <!-- foreach -->
                <th rowspan="2" colspan="3" style="text-align: center;"><strong>TOTAL</strong></th>
            </tr>
            <tr>
                @foreach ($headers as $key2 => $value2)
                    @foreach ($value2['age_category'] as $key3 => $value3)
                        <th colspan="3" style="text-align:center;">{{ $key3 }}</th>
                    @endforeach
                @endforeach
            </tr>
            <tr>
                <!-- foreach -->
                @foreach ($headers as $key => $value2)
                    @foreach ($value2['age_category'] as $key => $value3)
                        <th style="text-align:center;">E</th>
                        <th style="text-align:center;">P</th>
                        <th style="text-align:center;">PR</th>
                    @endforeach
                @endforeach
                <!-- endforeach -->
                <th style="text-align:center;">E</th>
                <th style="text-align:center;">P</th>
                <th style="text-align:center;">PR</th>
            </tr>

            @php($i = 1)
            @foreach ($datatables as $key => $data)
                <tr>
                    <td style="text-align:center;">{{ $i }}</td>
                    <td>{{ $data['club_name'] }}</td>
                    @foreach ($data['medal_array'] as $item)
                        <td style="text-align:center;">{{ $item }}</td>
                    @endforeach
                    <td>{{ $data['total_gold'] }}</td>
                    <td>{{ $data['total_silver'] }}</td>
                    <td>{{ $data['total_bronze'] }}</td>
                </tr>
                @php($i++)
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
  </div>
      </body>
</html>