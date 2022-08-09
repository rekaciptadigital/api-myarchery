<!DOCTYPE html>
<html>
  <head>
    <title>Page Title</title>
    <style type="text/css" >
    div.page
    {
        page-break-after: always;
        page-break-inside: avoid;
        break-after:page;
		float:none;
    overflow: visible;
    }
</style>
  </head>
  <body>
    <div class="page" style="break-after:page">
<img src="https://i.postimg.cc/ZRR5vW05/header.png" alt="Trulli" width="100%">
      <h1 style="text-align: center">{{$report}}</h1>
      <h1 style="text-align: center">{{$category}}</h1>
      <table style="width:100%;border: 1px solid black;">
        <thead>
          <!-- <tr><th>Table Heading</th></tr> -->
        </thead>
        <tbody>
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
          </tr> @foreach ($data_report as $data) <tr style="border: 1px solid black;">
            <td style="text-align: center;border: 1px solid black;">{{ $data['category'] ? $data['category'] : '-' }}</td>
            <td style="text-align: center;border: 1px solid black;">{{ $data['date'] ? $data['date'] : '-' }}</td>
            <td style="text-align: left;border: 1px solid black;">{{ $data['medal'] ? $data['medal'] : '-' }} </td>
            <td style="text-align: center;border: 1px solid black;">{{ $data['athlete'] ? $data['athlete'] : '-' }}</td>
            <td style="text-align: center;border: 1px solid black;">{{ $data['club'] ? $data['club'] : '-' }}</td>
          </tr> @endforeach
        </tbody>
      </table>
  </div>
      </body>
</html>