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

<h1 style="text-align: center">{{$report}}</h1>
<h1 style="text-align: center">{{$category}}</h1>
<table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody style="font-size: 24px;">
        
            <tr  style="border: 1px solid black;">
               
                
                <th style="text-align: center; border: 1px solid black;"><strong>NAME</strong></th>
                <th style="text-align: center;border: 1px solid black; "><strong>CLUB</strong></th>
                <th style="text-align: center;border: 1px solid black; "><strong>SESI 1</strong></th>
                <th style="text-align: center;border: 1px solid black; "><strong>SESI 2</strong></th>
                <th style="text-align: center;border: 1px solid black; "><strong>TOTAL</strong></th>
                <th style="text-align: center;border: 1px solid black; "><strong>X</strong></th>
                <th style="text-align: center;border: 1px solid black; "><strong>X+10</strong></th>
                
            </tr>
          @foreach ($data_report as $data)
            <tr style="border: 1px solid black;">
            
                <td style="text-align: center;border: 1px solid black;">{{ $data['athlete'] ? $data['athlete'] : '-' }}</td>
                <td style="text-align: center;border: 1px solid black;">{{ $data['club'] ? $data['club'] : '-' }}</td>
                <td style="text-align: center;border: 1px solid black;">{{ $data['scoring']['sessions']['1'] ? $data['scoring']['sessions']['1']['total'] : '-' }}</td>
                <td style="text-align: center;border: 1px solid black;">{{ $data['scoring']['sessions']['2'] ? $data['scoring']['sessions']['2']['total'] : '-' }}</td>
                <td style="text-align: center;border: 1px solid black;">{{ $data['scoring'] ? $data['scoring']['total'] : '-' }}</td>
                <td style="text-align: center;border: 1px solid black;">{{ $data['scoring'] ? $data['scoring']['total_x'] : '-' }}</td>
                <td style="text-align: center;border: 1px solid black;">{{ $data['scoring'] ? $data['scoring']['total_x_plus_ten'] : '-' }}</td>
               

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