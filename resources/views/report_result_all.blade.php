<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
</head>
<body>
<img src="https://i.postimg.cc/ZRR5vW05/header.png" alt="Trulli" width="100%">

<h1 style="text-align: center">{{$report}}</h1>
<h1 style="text-align: center">{{$category}}</h1>
<table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
        
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
                <td style="text-align: center;border: 1px solid black;"></td>
                <td style="text-align: center;border: 1px solid black;"> </td>
                <td style="text-align: center;border: 1px solid black;"></td>
                <td style="text-align: center;border: 1px solid black;"></td>
                <td style="text-align: center;border: 1px solid black;"></td>
               

            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>
</html>