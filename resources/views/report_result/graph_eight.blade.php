<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bracket</title>
    <style>
        .vl {
            border-left: 2px solid black;
            height: 100px;
        }

        .bracket {
            display: inline-block;
            white-space: nowrap;
            font-size: 0;
            font-family: 'Inter';
        }

        .bracket .round {
            display: inline-block;
            vertical-align: middle;
        }

        .bracket .round .winners>div {
            display: inline-block;
            vertical-align: middle;
        }

        .bracket .round .winners>div.matchups .matchup:last-child {
            margin-bottom: 0 !important;
        }

        .bracket .round .winners>div.matchups .matchup .participants {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 0 1px #000000;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant32 {
            box-sizing: border-box;
            color: #000000;
            background: white;
            width: 10rem;
            height: 2rem;
            box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.12);
            text-align: left;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant32.winner {
            color: #1f3d7a;
            border-color: #1f3d7a;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant32.loser {
            color: #dc563f;
            border-color: #dc563f;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant32:not(:last-child) {
            border-bottom: thin solid #f0f2f2;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant32 span.participant_name {
            overflow: hidden;
            width: 100px;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 0 0.5rem;
            line-height: 2;
            font-size: 11px;
            font-family: 'Inter';
            display: inline-block;
        }

        .bracket .round .winners>div.connector.filled .line,
        .bracket .round .winners>div.connector.filled.bottom .merger::after,
        .bracket .round .winners>div.connector.filled.top .merger::before {
            border-color: #1f3d7a;
        }

        .bracket .round .winners>div.connector .line,
        .bracket .round .winners>div.connector .merger {
            box-sizing: border-box;
            width: 2rem;
            display: inline-block;
            vertical-align: top;
        }

        .bracket .round .winners>div.connector .line {
            height: 2rem;
        }

        .bracket .round .winners>div.connector .merger {
            position: relative;
            height: 5rem;
        }

        .bracket .round .winners>div.connector .merger::before,
        .bracket .round .winners>div.connector .merger::after {
            content: "";
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: 50%;
            border: 0 solid;
            border-color: #000000;
        }

        .bracket .round .winners>div.connector .merger::before {
            border-right-width: thin;
            border-top-width: thin;
        }

        .bracket .round .winners>div.connector .merger::after {
            border-right-width: thin;
            border-bottom-width: thin;
        }

        .bracket .round.best-32 .winners:not(:last-child) {
            margin-bottom: 1rem;
        }

        .bracket .round.best-32 .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 1rem;
        }

        .bracket .round.best-32 .label-group-match {
            font-size: 13px;
            font-weight: bold;
            background-color: #009EFF;
            color: white;
            padding-top: 5px;
            padding-right: auto;
            padding-left: auto;
            padding-bottom: 5px;
            text-align: center;
            width: 10rem;
            border-radius: 10px;
            align-items: left;
            margin-bottom: 0.3rem;
        }

        .bracket .round.best-16 .winners:not(:last-child) {
            margin-bottom: 3rem;
        }

        .bracket .round.best-16 .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 3rem;
        }

        .bracket .round.best-16 .winners .connector .merger {
            height: 10rem;
        }

        .bracket .round.best-16 .winners .connector .line {
            height: 8rem;
        }

        .bracket .round.best-16 .label-group-match {
            font-size: 13px;
            font-weight: bold;
            background-color: #009EFF;
            color: white;
            padding-top: 5px;
            padding-right: auto;
            padding-left: auto;
            padding-bottom: 5px;
            text-align: center;
            width: 10rem;
            border-radius: 10px;
            align-items: left;
            margin-bottom: 0.3rem;
        }

        .bracket .round.quarterfinals .winners:not(:last-child) {
            margin-bottom: 8rem;
        }

        .bracket .round.quarterfinals .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 13rem;
        }

        .bracket .round.quarterfinals .winners .connector .merger {
            height: 21rem;
        }

        .bracket .round.quarterfinals .winners .connector .line {
            height: 8rem;
        }

        .bracket .round.quarterfinals .label-group-match {
            font-size: 13px;
            font-weight: bold;
            background-color: #009EFF;
            color: white;
            padding-top: 5px;
            padding-right: auto;
            padding-left: auto;
            padding-bottom: 5px;
            text-align: center;
            width: 10rem;
            border-radius: 10px;
            align-items: left;
            margin-bottom: 0.3rem;
        }

        .bracket .round.semifinals .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 14rem;
        }

        .bracket .round.semifinals .winners .connector .merger {
            height: 38rem;
        }

        .bracket .round.semifinals .winners .connector .line {
            height: 16rem;
        }

        .bracket .round.semifinals .label-group-match {
            font-size: 13px;
            font-weight: bold;
            background-color: #009EFF;
            color: white;
            padding-top: 5px;
            padding-right: auto;
            padding-left: auto;
            padding-bottom: 5px;
            text-align: center;
            width: 10rem;
            border-radius: 10px;
            align-items: left;
            margin-bottom: 0.3rem;
        }

        .bracket .round.semifinals .title {
            font-size: 15px;
            text-align: center;
        }

        .bracket .round.finals .winners .connector .merger {
            height: 3rem;
        }

        .bracket .round.finals .winners .connector .merger::before,
        .bracket .round.finals .winners .connector .merger::after {
            border-color: transparent;
        }

        .bracket .round.finals .winners .connector .line {
            height: 1.5rem;
            border-color: transparent;
        }

        .bracket .round.finals .label-group-match {
            font-size: 13px;
            font-weight: bold;
            background-color: #009EFF;
            color: white;
            padding-top: 5px;
            padding-right: auto;
            padding-left: auto;
            padding-bottom: 5px;
            text-align: center;
            width: 10rem;
            border-radius: 10px;
            align-items: left;
            margin-bottom: 0.3rem;
        }

        .bracket .round.finals .title {
            font-size: 15px;
            text-align: center;
            margin-left: -50px;
        }

        .styling {
            border: 1px solid;
            background: black;
            border-spacing: 0;
            float: right;
            height: 20px;
            width: 15px;
            text-align: center;
            padding-top: -60px;
        }

        .log_output_32 {
            color: white;
            float: right;
            font-size: 10.5px;
            margin-top: 2px;
            margin-bottom: -5px;
            margin-right: 5px;
            width: 25px;
            position: relative;
            height: 20px;
            text-align: center;
            border-radius: 0.5rem;
            display: inline-block;
        }
    </style>
</head>

<body translate="no">
    <div class="page" style="break-after:page">
        <!-- <img src="https://i.postimg.cc/ZRR5vW05/header.png" alt="Trulli" width="100%">  -->
        <table style="width: 100%; height: 20px;" border="0">
            <tbody>
                <tr style="height: 20px;">
                    <td style="width: 1%; height: 25px;" rowspan="2"></td>
                    <td style="width: 10%; height: 25px;" rowspan="2">
                        <img src="{{ $logo_event }}" alt="" srcset="" width="80%">
                    </td>
                    <td style="width: 10%; height: 25px;" rowspan="2">
                        <img src="https://api.myarchery.id/storage/logo/logo-archery.png" alt="" srcset=""
                            width="80%">
                    </td>
                    <td style="width: 1%; height: 25px;" rowspan="2"></td>
                    <td style="width: 42%; height: 25px; ">
                        <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                            <strong><span style="font-size: 30px;">{{ $event_name_report }}</span></strong> <br /><br />
                            {{ $event_location_report }}<br />
                            {{ $event_date_report }}
                        </p>
                    </td>
                    <td style="width: 2%; height: 25px;" rowspan="2">
                        <div class="vl"></div>
                    </td>
                    <td style="width: 10%; height: 25px; ">
                        <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                            {{ $competition }}<br />
                            Elimination<br />
                            Round<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="height:3px;border:none;color:black;background-color:black;" />
        <br>
        <h1 style="text-align: center;margin-top:0;padding-top:0;margin-bottom:0; padding-bottom:0;">{{ $category }}
        </h1>
        <br>

        <div class="bracket" style="margin-top: 0;margin-bottom:0;padding-top:0;padding-bottom:0">
            <section class="round quarterfinals">
                <div class="label-group-match">
                    1/8
                </div>
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round1member1status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member1result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member1result !!}</span>
                                    </div>
                                @endif


                                @if ($round1member2status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member2result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member2result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="matchup">
                            <div class="participants">
                                @if ($round1member3status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member3 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member3result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member3 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member3result !!}</span>
                                    </div>
                                @endif


                                @if ($round1member4status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member4 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member4result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member4 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member4result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="connector">
                        <div class="merger"></div>
                        <div class="line"></div>
                    </div>
                </div>
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round1member5status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member5 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member5result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member5 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member5result !!}</span>
                                    </div>
                                @endif


                                @if ($round1member6status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member6 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member6result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member6 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member6result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="matchup">
                            <div class="participants">
                                @if ($round1member7status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member7 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member7result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member7 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member7result !!}</span>
                                    </div>
                                @endif

                                @if ($round1member8status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member8 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member8result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member8 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member8result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="connector">
                        <div class="merger"></div>
                        <div class="line"></div>
                    </div>
                </div>
            </section>
            <section class="round semifinals">
                <div class="label-group-match">
                    Semi
                </div>
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round2member1status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round2member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round2member1result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round2member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round2member1result !!}</span>
                                    </div>
                                @endif


                                @if ($round2member2status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round2member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round2member2result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round2member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round2member2result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="matchup">
                            <div class="participants">
                                @if ($round2member3status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round2member3 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round2member3result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round2member3 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round2member3result !!}</span>
                                    </div>
                                @endif


                                @if ($round2member4status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round2member4 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round2member4result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round2member4 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round2member4result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="connector">
                        <div class="merger"></div>
                        <div class="line"></div>
                    </div>
                </div>
            </section>
            <section class="round finals" style="margin-top: -15px;">
                <div class="label-group-match">
                    Final
                </div>
                <div class="title">
                    <p>Medali Emas</span>
                </div>
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round3member1status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round3member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round3member1result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round3member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round3member1result !!}</span>
                                    </div>
                                @endif


                                @if ($round3member2status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round3member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round3member2result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round3member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round3member2result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="connector">
                        <div class="merger"></div>
                        <div class="line"></div>
                    </div>
                </div>
            </section>
            <section class="round finals" style="margin-top: -15px;">
                <div class="label-group-match">
                    3rd Place
                </div>
                <div class="title">
                    <p>Medali Perunggu</span>
                </div>
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round4member1status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round4member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round4member1result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round4member1 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round4member1result !!}</span>
                                    </div>
                                @endif


                                @if ($round4member2status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round4member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round4member2result !!}</span>
                                    </div>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round4member2 !!}
                                        </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round4member2result !!}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="connector">
                        <div class="merger"></div>
                        <div class="line"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>

</html>
