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
    <td colspan="8"
                    style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">
                     
                    <strong>RINGKASAN PERTANDINGAN BABAK KUALIFIKASI {{ $event_name }}</strong></td>
                   
    </table>
    <table style="width: 100%; height: 70px;" border="0"><td colspan="6"></td></table>



    <table style="width:100%;border: 1px solid black;">
        <thead></thead>
        <tbody>
            <tr >
                <th style="text-align: center; background: #FFFF00;"><strong>RANK</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>KLUB</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SESI 1</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SESI 2</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SESI 3</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>X</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>X + 10</strong></th>
            </tr>
            
            @php ($i = 1)
            @foreach ($datas as $key => $data)
            <tr>
                <td style="text-align: center;">{{ $i }}</td>
                <td style="text-align: center;">{{ $data['member']['name'] }}</td>
                <td style="text-align: center;">{{ $data['member']['club_name'] }}</td>

                <td style="text-align: center;">
                    @if ($filter_session == "2" || $filter_session == "3")
                        -
                    @else
                        {{ $data['sessions']['1']['total'] }}
                    @endif
                </td>

                <td style="text-align: center;">
                    @if ($filter_session == "1" || $filter_session == "3")
                        -
                    @else
                        {{ $data['sessions']['2']['total'] }}
                    @endif
                </td>

                <td style="text-align: center;">
                    @if ($filter_session == "1" || $filter_session == "2")
                        -
                    @else
                        @if ($filter_session < $session_in_qualification)
                            -
                        @else
                            {{ $data['sessions']['3']['total'] }}
                        @endif
                    @endif
                </td>

                <td style="text-align: center;">
                    @if ($filter_session == "1")
                        {{ $data['sessions']['1']['total'] }}
                    @elseif ($filter_session == "2")
                        {{ $data['sessions']['2']['total'] }}
                    @elseif ($filter_session == "3")
                        {{ $data['sessions']['3']['total'] }}
                    @else
                        {{ $data['total'] }}
                    @endif
                </td>
                <td style="text-align: center;">
                    @if ($filter_session == "1")
                        {{ $data['sessions']['1']['total_x'] }}
                    @elseif ($filter_session == "2")
                        {{ $data['sessions']['2']['total_x'] }}
                    @elseif ($filter_session == "3")
                        {{ $data['sessions']['3']['total_x'] }}
                    @else
                        {{ $data['total_x'] }}
                    @endif
                </td>
                <td style="text-align: center;">
                    @if ($filter_session == "1")
                        {{ $data['sessions']['1']['total_x_plus_ten'] }}
                    @elseif ($filter_session == "2")
                        {{ $data['sessions']['2']['total_x_plus_ten'] }}
                    @elseif ($filter_session == "3")
                        {{ $data['sessions']['3']['total_x_plus_ten'] }}
                    @else
                        {{ $data['total_x_plus_ten'] }}
                    @endif
                </td>
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
