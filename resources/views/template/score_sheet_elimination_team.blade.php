<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            height: 842px;
            width: 595px;
            /* to centre page on screen*/
            margin-left: auto;
            margin-right: auto;
        }

        .header {
            width: 70%;
            align-content: center;
            margin: auto;
        }

        #col1 {
            float: left;
            width: 46%;
            align-content: center;
            align: center;
        }

        #col2 {
            float: left;
            width: 46%;
            align-content: center;
            align: center;
        }

        .beta td,
        .beta th {
            border: 1px solid black;
        }

        .img-bottom {
            align-self: flex-end;
        }
    </style>
    <title>Score Sheet Elimination</title>
</head>

<body>
    <div class="header">
        <table>
            <tbody>
                <tr style="height: 80px">
                    <td style="vertical-align: center">
                        <img src="https://myarchery.id/static/media/myachery.9ed0d268.png" alt=""
                            height="95" />
                    </td>
                    <td style="padding-left:0; padding-top:15px; vertical-align:top; width:65%; line-height: 1.6">
                        <h1 style="font-size: 14pt;">{{ $event_name }}</h1>
                        <p>{{ $location }}</p>
                    </td>
                    <td>
                        @if ($qr != '')
                            <img style="height: 150px" src="{{ $qr }}" alt="" srcset="">
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div align="center" style="margin-top:3%;margin-left:7%;">
        <div id="col1">
            <table class="" style="margin-right: 10px;border:1px solid black;width:95%;" cellspacing="0">
                <tr>
                    <td style="width:15%;">Tim</td>
                    <td style="width: 2%">:</td>
                    <td style="width: 50%">{{ $tim_1_name }}</td>
                    <td style="width: 15%"></td>
                    <td style="width: 25%; text-align:center; border-left:1px solid black;background-color: #808080">
                        Rank</td>
                </tr>
                <tr>
                    <td style="">Category</td>
                    <td>:</td>
                    <td>{{ $tim1_category }}</td>
                    <td style="width:200px;"></td>
                    <td style="font-size: 22pt; text-align:center; border-left:1px solid black;background-color: #808080;"
                        rowspan="3">{{ $tim_1_rank }}</td>
                </tr>
                <tr>
                    <td style="">Athlete</td>
                    <td>:</td>
                    <td>
                        @if ($athlete_1 != '')
                            @foreach ($athlete_1 as $item)
                                {{ $item }},
                            @endforeach
                        @endif
                    </td>
                    <td style="width:200px;"></td>
                </tr>
                <tr>
                    <td style="">Target</td>
                    <td>:</td>
                    <td>{{ $budrest_1 }}</td>
                    <td style="text-align:center;border: 1px solid black;border-right:none;">TARGET -</td>
                </tr>
            </table>
        </div>
        <div id="col2">
            <table class="" style="margin-right: 10px;border:1px solid black;width:95%;" cellspacing="0">
                <tr>
                    <td style="width:15%;">Tim</td>
                    <td style="width: 2%">:</td>
                    <td style="width: 50%">{{ $tim_2_name }}</td>
                    <td style="width: 15%"></td>
                    <td style="width: 25%; text-align:center; border-left:1px solid black;background-color: #808080">
                        Rank</td>
                </tr>
                <tr>
                    <td style="">Category</td>
                    <td>:</td>
                    <td>{{ $tim2_category }}</td>
                    <td style="width:200px;"></td>
                    <td style="font-size: 22pt; text-align:center; border-left:1px solid black;background-color: #808080;"
                        rowspan="3">{{ $tim_2_rank }}</td>
                </tr>
                <tr>
                    <td style="">Athlete</td>
                    <td>:</td>
                    <td>
                        @if ($athlete_2 != '')
                            @foreach ($athlete_2 as $item)
                                {{ $item }},
                            @endforeach
                        @endif
                    </td>
                    <td style="width:200px;"></td>
                </tr>
                <tr>
                    <td style="">Target</td>
                    <td>:</td>
                    <td>{{ $budrest_2 }}</td>
                    <td style="text-align:center;border: 1px solid black;border-right:none;">TARGET -</td>
                </tr>
            </table>
        </div>
    </div>
    <div align="center" style="margin-top:2%;margin-left:7%;">
        <div id="col1">
            <table class="beta" style="margin-right: 10px;width:100%;" cellspacing="0">
                <thead>
                    <tr style="border:1px solid black;">
                        <th style="width: 5%;border:none;"></th>
                        <th colspan=2
                            style="width:8%;background-color: #808080; text-align:center;border-right: none; margin-left: 30px; padding: 10px; font-size:10pt">
                            <span
                                style="border: 1px solid black; padding: 5px;margin-right:5px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>Winner
                        </th>
                        <th style="width: 10%;background-color: #808080;border-left: none; font-size:12pt"
                            colspan="9">1/4</th>
                    </tr>
                    <tr style="width: 10%;background-color: #808080;border:1px solid black;">
                        <th style="width: 5%;background-color: white;border:none;"></th>
                        <th style="width: 10%;font-size:12pt;">1</th>
                        <th style="width: 10%;font-size:12pt;">2</th>
                        <th style="width: 10%;font-size:12pt">3</th>
                        <th style="width: 10%;font-size:12pt;">4</th>
                        <th style="width: 10%;font-size:12pt;">5</th>
                        <th style="width: 10%;font-size:12pt;">6</th>
                        <th style="font-size:12pt">Set Total</th>
                        <th style="font-size:12pt" colspan="3">Set Points</th>
                        <th style="width: 21%; font-size:12pt">Total Set Points</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- baris 1 --}}
                    <tr style="border: 1px solid black;">
                        <th style="width:7%; font-size:13pt">1</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="width: 12%"></td>
                        <td style="width: 10%"></td>
                        <td style="width: 10%"></td>
                        <td style="width: 10%"></td>
                        <td></td>
                    </tr>

                    {{-- baris 2 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt;">2</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    {{-- baris 3 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt">3</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    {{-- baris 4 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt">4</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    {{-- baris 5 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt">5</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="11" align="right" style="padding-right:5px;border:none;font-size:13pt;">Total
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="col2">
            <table class="beta" style="margin-right: 10px;width:100%;" cellspacing="0">
                <thead>
                    <tr style="border:1px solid black;">
                        <th style="width: 5%;border:none;"></th>
                        <th colspan=2
                            style="width:8%;background-color: #808080; text-align:center;border-right: none; margin-left: 30px; padding: 10px; font-size:10pt">
                            <span
                                style="border: 1px solid black; padding: 5px;margin-right:5px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>Winner
                        </th>
                        <th style="width: 10%;background-color: #808080;border-left: none; font-size:12pt"
                            colspan="9">1/4</th>
                    </tr>
                    <tr style="width: 10%;background-color: #808080;border:1px solid black;">
                        <th style="width: 5%;background-color: white;border:none;"></th>
                        <th style="width: 10%;font-size:12pt;">1</th>
                        <th style="width: 10%;font-size:12pt;">2</th>
                        <th style="width: 10%;font-size:12pt">3</th>
                        <th style="width: 10%;font-size:12pt;">4</th>
                        <th style="width: 10%;font-size:12pt;">5</th>
                        <th style="width: 10%;font-size:12pt;">6</th>
                        <th style="font-size:12pt">Set Total</th>
                        <th style="font-size:12pt" colspan="3">Set Points</th>
                        <th style="width: 21%; font-size:12pt">Total Set Points</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- baris 1 --}}
                    <tr style="border: 1px solid black;">
                        <th style="width:7%; font-size:13pt">1</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="width: 12%"></td>
                        <td style="width: 10%"></td>
                        <td style="width: 10%"></td>
                        <td style="width: 10%"></td>
                        <td></td>
                    </tr>

                    {{-- baris 2 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt;">2</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    {{-- baris 3 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt">3</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    {{-- baris 4 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt">4</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    {{-- baris 5 --}}
                    <tr style="border: 1px solid black;">
                        <th style="font-size:13pt">5</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="11" align="right" style="padding-right:5px;border:none;font-size:13pt;">Total
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div align="left" style="margin-top:2%;">
        <div id="col1" style="margin-left:7%">
            <table style="" border=1 cellspacing="0">
                <tr>
                    <th rowspan=4>S.O.</th>
                    <th style="width:200px;height:20px;" colspan=2></th>
                </tr>
                <tr>
                    <td colspan=2 style="width:200px;height:20px;"></td>
                </tr>
                {{-- <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>
                </tr>
                <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>
                </tr> --}}
            </table>
            <p>Closest to the center</p>
        </div>
        <div id="col2" style="margin-left:-3.3%">
            <table style="" border=1 cellspacing="0">
                <tr>
                    <th rowspan=4>S.O.</th>
                    <th style="width:200px;height:20px;" colspan=2></th>
                </tr>
                <tr>
                    <td colspan=2 style="width:200px;height:20px;"></td>
                </tr>
                {{-- <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>
                </tr>
                <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>
                </tr> --}}
            </table>
            <p>Closest to the center</p>
        </div>
    </div>
    <div align="left" style="margin-left:2.5%;">
        <div id="col1" style="margin-left:4.7%;width:44%;">
            <p>Archer/Agent</p>

        </div>
        <div id="col2" style="margin-left:0.5%;width:44%;">
            <p>Archer/Agent</p>

        </div>

    </div>
    <hr style="width:85.5%">
    <table style="width:92.5%;margin-left:7%">
        <tr style="border-bottom: 2px solid black">
            <td colspan=2 style="width:70%;border-bottom: 2px solid black">Target Judge Signature </td>
            <td style="border:2px solid black"> Signature Timestamp (HH:MM)</td>
        </tr>
    </table>
    <table style="width:92.5%;margin-left:7%">
        <tr style="border-bottom: 2px solid black">
            <td style="border-bottom: 2px solid black">Anotations </td>
        </tr>
    </table>
    <table style="width:92.5%;margin-left:7%;height:10%;">
        <tr style="border-bottom: 2px solid black">
            <td style="border-bottom: 2px solid black;height:20px;"> </td>
        </tr>
    </table>
    <div align="center">
        <div id="col1">
            <p align="right" style="margin-right:-10%">7/12</p>
        </div>
        <div id="col2">
            <p align="right" style="margin-right:-2%">Individual Finals - 202203912</p>
        </div>
    </div>


</body>

</html>
