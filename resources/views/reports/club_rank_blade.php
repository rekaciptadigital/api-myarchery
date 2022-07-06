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
                     
                    <strong>MEDALS STANDING {{ $event_name }}</strong></td>
                   
    </table>
    <table style="width: 100%; height: 70px;" border="0"><td colspan="6"></td></table>



    <table style="width:100%;border: 1px solid black;">
        <thead></thead>
        <tbody>
            <tr >
                <th rowspan="3" style="text-align: center; background: #FFFF00;"><strong>NO</strong></th>
                <th rowspan="3" style="text-align: center; background: #FFFF00;"><strong>KLUB/KONTINGEN</strong></th>
                <!-- foreach -->
                @foreach ($data['deta'])
                <th colspan="variable" style="text-align: center; background: #FFFF00;"><strong>Recurve</strong></th>
                <!-- foreach -->
                <th rowspan="3" style="text-align: center; background: #FFFF00;"><strong>TOTAL</strong></th>
            </tr>
            <tr>
                <th colspan="3" style="text-align:center; background: #ffd68a;">U-12</th>
            </tr>
            <tr>
                <!-- foreach -->
                <th style="text-align:center; background: #ffd68a;">E</th>
                <th style="text-align:center; background: #ffd68a;">P</th>
                <th style="text-align:center; background: #ffd68a;">PR</th>
                <!-- endforeach -->
            </tr>
            
            @php ($i = 1)
            @foreach ($datas as $key => $data)
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
