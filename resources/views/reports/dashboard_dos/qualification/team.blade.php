<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
   
    <title>RINGKASAN KUALIFIKASI</title>
    
   
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>
</head>

<body>
<table style="width: 100%; height: 70px;" border="0">
    <td colspan="6"
                    style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">
                     
                    <strong>RINGKASAN PERTANDINGAN BABAK KUALIFIKASI {{ $event_name }}</strong></td>
                   <td></td>
    </table>
    <table style="width: 100%; height: 70px;" border="0"><td colspan="6"></td></table>



    <table style="width:100%;border: 1px solid black;">
        <thead></thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>RANK</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA TIM</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>KLUB</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>X</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>X + 10</strong></th>
            </tr>
            
            @php ($i = 1)
            @foreach ($datas as $key => $data)
            <tr>
                <td style="text-align: center; vertical-align: middle;">{{ $i }}</td>
                <td style="text-align: center;">
                    <p>{{ $data['team'] }}</p>
                    <ol>
                        @if (sizeof($data['teams']) > 0)
                            @foreach ($data['teams'] as $key => $team)
                                <li> {{ $team['name'] }} </li>
                            @endforeach
                        @else
                            <li> Belum ada anggota </li>
                        @endif
                    </ol>
                </td>
                <td style="text-align: center; vertical-align: middle;">{{ $data['club_name'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $data['total'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $data['total_x'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $data['total_x_plus_ten'] }}</td>
            </tr>
            @php ($i++)
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
