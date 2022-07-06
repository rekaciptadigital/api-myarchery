<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
   
    <title>MEDALS STANDING</title>
    
   
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
                     
                    <strong>MEDALS STANDING</strong></td>
                   
    </table>
    <table style="width: 100%; height: 70px;" border="0"><td colspan="6"></td></table>



    <table style="width:100%;border: 1px solid black;">
        <thead></thead>
        <tbody>
            <tr >
                <th rowspan="3" style="text-align: center;"><strong>NO</strong></th>
                <th rowspan="3" style="text-align: center;"><strong>KLUB/KONTINGEN</strong></th>
                <!-- foreach -->
                @foreach ($headers as $key => $value)
                <th colspan="{{ $value[0]['count_colspan'] }}" style="text-align: center;"><strong>{{ $key }}</strong></th>
                @endforeach
                <!-- foreach -->
                <th rowspan="3" style="text-align: center;"><strong>TOTAL</strong></th>
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
            </tr>
            
            @php ($i = 1)
            @foreach ($datatables as $key => $data)
            <tr>
               
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
