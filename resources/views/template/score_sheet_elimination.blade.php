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
                        <img src="https://myarchery.id/static/media/myachery.9ed0d268.png" alt="" height="95" />
                    </td>
                    <td style="padding-left:0; padding-top:15px; vertical-align:top; width:65%; line-height: 1.6">
                        <h1 style="font-size: 14pt;">KEJOHANAN MEMANAH TERBUKA PDAC - SULI ARCHERY 2</h1>
                        <p>KELAB MEMANAH PD (PDA2019)</p>
                        <p>LAPANG SASAR MEMANAH SMK KAMPUNG BARU SI RUSA, PORT DICKSON, NEGERI SEMBILAN, </p>
                    </td>
                    <td>
                        <img style="height: 150px"
                            src="data:image/png;base64,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"
                            alt="" srcset="">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div align="center" style="margin-top:3%;margin-left:7%;">
        <div id="col1">
            <table class="" style="margin-right: 10px;border:1px solid black;width:95%;" cellspacing="0">
                <tr>
                    <td style="width:15%;">Athlete</td>
                    <td style="width: 2%">:</td>
                    <td style="width: 50%">{{ $peserta1_name }}</td>
                    <td style="width: 15%"></td>
                    <td style="width: 25%; text-align:center; border-left:1px solid black;background-color: #808080">
                        Rank</td>
                </tr>
                <tr>
                    <td>Country</td>
                    <td>:</td>
                    <td>{{ $peserta1_club }}</td>
                    <td></td>
                    <td style="font-size: 22pt; text-align:center; border-left:1px solid black;background-color: #808080;"
                        rowspan="3">{{ $peserta1_rank }}</td>
                </tr>
                <tr>
                    <td style="">Category</td>
                    <td>:</td>
                    <td>{{ $peserta1_category }}</td>
                    <td style="width:200px;"></td>
                </tr>
                <tr>
                    <td style="">Target</td>
                    <td>:</td>
                    <td>-</td>
                    <td style="text-align:center;border: 1px solid black;border-right:none;">TARGET -</td>
                </tr>
            </table>
        </div>
        <div id="col2">
            <table class="" style="margin-right: 10px;border:1px solid black;width:95%;" cellspacing="0">
                <tr>
                    <td style="width:15%;">Athlete</td>
                    <td style="width: 2%">:</td>
                    <td style="width: 50%">{{ $peserta2_name }}</td>
                    <td style="width: 15%"></td>
                    <td style="width: 25%; text-align:center; border-left:1px solid black;background-color: #808080">
                        Rank</td>
                </tr>
                <tr>
                    <td>Country</td>
                    <td>:</td>
                    <td>{{ $peserta2_club }}</td>
                    <td></td>
                    <td style="font-size: 22pt; text-align:center; border-left:1px solid black;background-color: #808080;"
                        rowspan="3">{{ $peserta2_rank }}</td>
                </tr>
                <tr>
                    <td style="">Category</td>
                    <td>:</td>
                    <td>{{ $peserta2_category }}</td>
                    <td style="width:200px;"></td>
                </tr>
                <tr>
                    <td style="">Target</td>
                    <td>:</td>
                    <td>-</td>
                    <td style="text-align:center;border: 1px solid black;border-right:none;">TARGET -</td>
                </tr>
            </table>
        </div>
    </div>
    <div align="center" style="margin-top:2%;margin-left:7%;">
        <div id="col1">
            <table class="beta" style="margin-right: 10px;width:97%;" cellspacing="0">
                <thead>
                    <tr style="border:1px solid black;">
                        <th style="width: 5%;border:none;"></th>
                        <th colspan=2
                            style="width:8%;background-color: #808080; text-align:center;border-right: none; margin-left: 30px; padding: 10px;">
                            <span
                                style="border: 1px solid black; padding: 5px;margin-right:5px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>Winner
                        </th>
                        <th style="width: 10%;background-color: #808080;border-left: none" colspan="7">1/4</th>
                    </tr>
                    <tr style="width: 10%;background-color: #808080;border:1px solid black;">
                        <th style="width: 5%;background-color: white;border:none;"></th>
                        <th style="width: 10%">1</th>
                        <th style="width: 10%">2</th>
                        <th style="width: 10%">3</th>
                        <th>Set Total</th>
                        <th colspan="3">Set Points</th>
                        <th>Total Set Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    $point = 0; ?>

                    @foreach ($score1 as $score)
                        <tr style="border: 1px solid black;">
                            <th>{{ $i++ }}</th>
                            <td><?php print_r($score['score'][0]); ?></td>
                            <td><?php print_r($score['score'][1]); ?></td>
                            <td><?php print_r($score['score'][2]); ?></td>
                            <td><?php print_r($score['total']); ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @if (isset($score['point']))
                                <td>{{ $score['point'] }}</td>
                                @php
                                    $point = $point + $score['point'];
                                @endphp
                            @else
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="8" align="right" style="padding-right:5px;border:none;">Total</td>
                        <td>{{ $point }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="col2">
            <table class="beta" style="margin-right: 10px;width:97%;" cellspacing="0">
                <thead>
                    <tr style="border:1px solid black;">
                        <th style="width: 5%;border:none;"></th>
                        <th colspan=2
                            style="width:8%;background-color: #808080; text-align:center;border-right: none; margin-left: 30px; padding: 10px;">
                            <span
                                style="border: 1px solid black; padding: 5px;margin-right:5px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>Winner
                        </th>
                        <th style="width: 10%;background-color: #808080;border-left: none" colspan="7">1/4</th>
                    </tr>
                    <tr style="width: 10%;background-color: #808080;border:1px solid black;">
                        <th style="width: 5%;background-color: white;border:none;"></th>
                        <th style="width: 10%">1</th>
                        <th style="width: 10%">2</th>
                        <th style="width: 10%">3</th>
                        <th>Set Total</th>
                        <th colspan="3">Set Points</th>
                        <th>Total Set Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    $point = 0; ?>

                    @foreach ($score2 as $score)
                        <tr style="border: 1px solid black;">
                            <th>{{ $i++ }}</th>
                            <td><?php print_r($score['score'][0]); ?></td>
                            <td><?php print_r($score['score'][1]); ?></td>
                            <td><?php print_r($score['score'][2]); ?></td>
                            <td><?php print_r($score['total']); ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @if (isset($score['point']))
                                <td>{{ $score['point'] }}</td>
                                @php
                                    $point = $point + $score['point'];
                                @endphp
                            @else
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="8" align="right" style="padding-right:5px;border:none;">Total</td>
                        <td>{{ $point }}</td>
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
                <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>
                </tr>
                <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>

                </tr>
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
                <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>
                </tr>
                <tr>
                    <td style="width:200px;height:20px;" coslpan=2> </td>

                </tr>
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
