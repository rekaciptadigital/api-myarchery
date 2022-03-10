<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   
    <title>{{substr(str_replace("Individu","",$category),0,30)}}</title>
    
   
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>
</head>

<body>
<table style="width: 100%; height: 70px;" border="0">
    <td colspan="7" style="text-align: center;">
            <strong>    
                <h1>{{$category}}</h1>
            </strong>
    </td>
                   
    </table>



    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
            <strong>    
                {{$category}}
            </strong>
            </tr> -->
        </thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>Peringkat</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Nama</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>email</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Asal Kota Madya</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Poin Kualifikasi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Poin Eliminasi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Point</strong></th>
            </tr>
            @foreach ($datas as $data)
            <tr>
                <td style="text-align: center;">{{ $data['pos'] ? $data['pos'] : '-' }}</td>
                <td style="text-align: left;">{{ $data['name'] ? $data['name'] : '-' }}  </td>
                <td style="text-align: left;">{{ $data['email'] ? $data['email'] : '' }}</td>
                <td style="text-align: left;">{{ $data['city'] ? $data['city'] : '' }}</td>
                <td style="text-align: center;">{{ $data['point_qualification'] ? $data['point_qualification'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['point_elimination'] ? $data['point_elimination'] : '0' }}</td>
                <td style="text-align: center;">{{ $data['total_point'] ? $data['total_point'] : '0' }}</td>
            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
