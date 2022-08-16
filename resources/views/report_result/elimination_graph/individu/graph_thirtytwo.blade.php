<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bracket</title>
    <style>
        .vl {
            border-left: 2px solid black;
            height: 150px;
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
            height: 4rem;
        }

        .bracket .round .winners>div.connector .merger {
            position: relative;
            height: 7rem;
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

        .bracket .round.best-16-of-32 .winners:not(:last-child) {
            margin-bottom: 6rem;
        }

        .bracket .round.best-16-of-32 .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 6rem;
        }

        .bracket .round.best-16-of-32 .winners .connector .merger {
            height: 10rem;
        }

        .bracket .round.best-16-of-32 .winners .connector .line {
            height: 8rem;
        }

        .bracket .round.quarterfinals-of-32 .winners:not(:last-child) {
            margin-bottom: 16rem;
        }

        .bracket .round.quarterfinals-of-32 .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 17rem;
        }

        .bracket .round.quarterfinals-of-32 .winners .connector .merger {
            height: 21rem;
        }

        .bracket .round.quarterfinals-of-32 .winners .connector .line {
            height: 8rem;
        }

        .bracket .round.semifinals32 .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 14rem;
        }

        .bracket .round.semifinals32 .winners .connector .merger {
            height: 38rem;
        }

        .bracket .round.semifinals32 .winners .connector .line {
            height: 16rem;
        }

        .bracket .round.semifinals32 .title {
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
            height: 40px;
            width: 30px;
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
        <table style="width: 100%; height: 40px;" border="0">
            <tbody>
                <tr style="height: 40px;">
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        <img src="{{ $logo_event }}" alt="" srcset="" width="80%">
                    </td>
                    <td style="width: 10%; height: 50px;" rowspan="2">{{ $logo_archery }}</td>
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 42%; height: 50px; ">
                        <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                            <strong><span style="font-size: 30px;">{{ $event_name_report }}</span></strong> <br /><br />
                            {{ $event_location_report }}<br />
                            {{ $event_date_report }}
                        </p>
                    </td>
                    <td style="width: 2%; height: 50px;" rowspan="2">
                        <div class="vl"></div>
                    </td>
                    <td style="width: 10%; height: 50px; ">
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
        <p style="text-align: center; font-size: 30px;"><strong>{{ $category }}</strong></p>
        <h2 style="text-align: center">Elimination & Final (Bracket)</h2>
        <div class="bracket">
            <section class="round best-32">
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round1member1status === 'win')
                                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span class="participant_name"> {!! $round1member1 !!} </span>
                                        <span class="log_output_32"
                                            style="background:black;">{!! $round1member1result !!}</span>
                                    @else
                                        <div class="participant32" style="background:white;border:1.8px solid gray">
                                            <span class="participant_name"> {!! $round1member1 !!} </span>
                                            <span class="log_output_32"
                                                style="background:gray;">{!! $round1member1result !!}</span>
                                @endif
                            </div>

                            @if ($round1member2status === 'win')
                                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                    <span class="participant_name"> {!! $round1member2 !!} </span>
                                    <span class="log_output_32" style="background:black;">{!! $round1member2result !!}</span>
                                @else
                                    <div class="participant32" style="background:white;border:1.8px solid gray">
                                        <span class="participant_name"> {!! $round1member2 !!} </span>
                                        <span class="log_output_32"
                                            style="background:gray;">{!! $round1member2result !!}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="matchup">
                    <div class="participants">
                        @if ($round1member3status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round1member3 !!} </span>
                                <span class="log_output_32" style="background:black;">{!! $round1member3result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round1member3 !!} </span>
                                    <span class="log_output_32" style="background:gray;">{!! $round1member3result !!}</span>
                        @endif
                    </div>

                    @if ($round1member4status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member4 !!} </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member4result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member4 !!} </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member4result !!}</span>
                    @endif
                </div>
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
                            <span class="participant_name"> {!! $round1member5 !!} </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member5result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member5 !!} </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member5result !!}</span>
                    @endif
                </div>

                @if ($round1member6status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member6 !!} </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member6result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member6 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member6result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member7status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member7 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member7result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member7 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member7result !!}</span>
            @endif
        </div>

        @if ($round1member8status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member8 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member8result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member8 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member8result !!}</span>
        @endif
    </div>
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
                    @if ($round1member9status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member9 !!} </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member9result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member9 !!} </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member9result !!}</span>
                    @endif
                </div>

                @if ($round1member10status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member10 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member10result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member10 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member10result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member11status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member11 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member11result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member11 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member11result !!}</span>
            @endif
        </div>

        @if ($round1member12status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member12 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member12result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member12 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member12result !!}</span>
        @endif
    </div>
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
                    @if ($round1member13status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member13 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member13result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member13 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member13result !!}</span>
                    @endif
                </div>

                @if ($round1member14status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member14 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member14result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member14 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member14result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member15status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member15 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member15result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member15 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member15result !!}</span>
            @endif
        </div>

        @if ($round1member16status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member16 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member16result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member16 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member16result !!}</span>
        @endif
    </div>
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
                    @if ($round1member17status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member17 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member17result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member17 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member17result !!}</span>
                    @endif
                </div>

                @if ($round1member18status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member18 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member18result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member18 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member18result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member19status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member19 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member19result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member19 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member19result !!}</span>
            @endif
        </div>

        @if ($round1member20status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member20 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member20result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member20 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member20result !!}</span>
        @endif
    </div>
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
                    @if ($round1member21status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member21 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member21result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member21 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member21result !!}</span>
                    @endif
                </div>

                @if ($round1member22status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member22 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member22result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member22 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member22result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member23status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member23 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member23result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member23 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member23result !!}</span>
            @endif
        </div>

        @if ($round1member24status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member24 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member24result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member24 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member24result !!}</span>
        @endif
    </div>
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
                    @if ($round1member25status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member25 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member25result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member25 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member25result !!}</span>
                    @endif
                </div>

                @if ($round1member26status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member26 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member26result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member26 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member26result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member27status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member27 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member27result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member27 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member27result !!}</span>
            @endif
        </div>

        @if ($round1member28status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member28 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member28result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member28 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member28result !!}</span>
        @endif
    </div>
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
                    @if ($round1member29status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round1member29 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round1member29result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round1member29 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round1member29result !!}</span>
                    @endif
                </div>

                @if ($round1member30status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round1member30 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round1member30result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round1member30 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round1member30result !!}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member31status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round1member31 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round1member31result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round1member31 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round1member31result !!}</span>
            @endif
        </div>

        @if ($round1member32status === 'win')
            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span class="participant_name"> {!! $round1member32 !!}
                </span>
                <span class="log_output_32" style="background:black;">{!! $round1member32result !!}</span>
            @else
                <div class="participant32" style="background:white;border:1.8px solid gray">
                    <span class="participant_name"> {!! $round1member32 !!}
                    </span>
                    <span class="log_output_32" style="background:gray;">{!! $round1member32result !!}</span>
        @endif
    </div>
    </div>
    </div>
    </div>
    <div class="connector">
        <div class="merger"></div>
        <div class="line"></div>
    </div>
    </div>
    </section>
    <section class="round best-16-of-32">
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round2member1status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round2member1 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round2member1result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round2member1 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round2member1result !!}</span>
                        @endif
                    </div>

                    @if ($round2member2status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round2member2 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round2member2result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round2member2 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round2member2result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round2member3status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round2member3 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round2member3result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round2member3 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round2member3result !!}</span>
                @endif
            </div>

            @if ($round2member4status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round2member4 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round2member4result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round2member4 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round2member4result !!}</span>
            @endif
        </div>
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
                        @if ($round2member5status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round2member5 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round2member5result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round2member5 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round2member5result !!}</span>
                        @endif
                    </div>

                    @if ($round2member6status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round2member6 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round2member6result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round2member6 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round2member6result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round2member7status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round2member7 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round2member7result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round2member7 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round2member7result !!}</span>
                @endif
            </div>

            @if ($round2member8status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round2member8 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round2member8result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round2member8 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round2member8result !!}</span>
            @endif
        </div>
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
                        @if ($round2member9status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round2member9 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round2member9result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round2member9 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round2member9result !!}</span>
                        @endif
                    </div>

                    @if ($round2member10status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round2member10 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round2member10result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round2member10 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round2member10result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round2member11status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round2member11 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round2member11result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round2member11 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round2member11result !!}</span>
                @endif
            </div>

            @if ($round2member12status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round2member12 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round2member12result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round2member12 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round2member12result !!}</span>
            @endif
        </div>
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
                        @if ($round2member13status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round2member13 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round2member13result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round2member13 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round2member13result !!}</span>
                        @endif
                    </div>

                    @if ($round2member14status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round2member14 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round2member14result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round2member14 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round2member14result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round2member15status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round2member15 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round2member15result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round2member15 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round2member15result !!}</span>
                @endif
            </div>

            @if ($round2member16status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round2member16 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round2member16result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round2member16 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round2member16result !!}</span>
            @endif
        </div>
        </div>
        </div>
        </div>
        <div class="connector">
            <div class="merger"></div>
            <div class="line"></div>
        </div>
        </div>
    </section>
    <section class="round quarterfinals-of-32">
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round3member1status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round3member1 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round3member1result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round3member1 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round3member1result !!}</span>
                        @endif
                    </div>

                    @if ($round3member2status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round3member2 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round3member2result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round3member2 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round3member2result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round3member3status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round3member3 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round3member3result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round3member3 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round3member3result !!}</span>
                @endif
            </div>

            @if ($round3member4status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round3member4 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round3member4result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round3member4 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round3member4result !!}</span>
            @endif
        </div>
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
                        @if ($round3member5status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round3member5 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round3member5result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round3member5 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round3member5result !!}</span>
                        @endif
                    </div>

                    @if ($round3member6status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round3member6 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round3member6result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round3member6 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round3member6result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round3member7status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round3member7 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round3member7result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round3member7 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round3member7result !!}</span>
                @endif
            </div>

            @if ($round3member8status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round3member8 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round3member8result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round3member8 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round3member8result !!}</span>
            @endif
        </div>
        </div>
        </div>
        </div>
        <div class="connector">
            <div class="merger"></div>
            <div class="line"></div>
        </div>
        </div>
    </section>
    <section class="round semifinals32">
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round4member1status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round4member1 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round4member1result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round4member1 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round4member1result !!}</span>
                        @endif
                    </div>

                    @if ($round4member2status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round4member2 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round4member2result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round4member2 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round4member2result !!}</span>
                    @endif
                </div>
            </div>
        </div>
        <!-- medal bronze -->
        <div class="title">
            <p>Medali Perunggu</span>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round6member1status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round6member1 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round6member1result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round6member1 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round6member1result !!}</span>
                @endif
            </div>

            @if ($round6member2status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round6member2 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round6member2result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round6member2 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round6member2result !!}</span>
            @endif
        </div>
        </div>
        </div>
        <!-- end medal bronze -->
        <div class="matchup">
            <div class="participants">
                @if ($round4member3status === 'win')
                    <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span class="participant_name"> {!! $round4member3 !!}
                        </span>
                        <span class="log_output_32" style="background:black;">{!! $round4member3result !!}</span>
                    @else
                        <div class="participant32" style="background:white;border:1.8px solid gray">
                            <span class="participant_name"> {!! $round4member3 !!}
                            </span>
                            <span class="log_output_32" style="background:gray;">{!! $round4member3result !!}</span>
                @endif
            </div>

            @if ($round4member4status === 'win')
                <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span class="participant_name"> {!! $round4member4 !!}
                    </span>
                    <span class="log_output_32" style="background:black;">{!! $round4member4result !!}</span>
                @else
                    <div class="participant32" style="background:white;border:1.8px solid gray">
                        <span class="participant_name"> {!! $round4member4 !!}
                        </span>
                        <span class="log_output_32" style="background:gray;">{!! $round4member4result !!}</span>
            @endif
        </div>
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
        <div class="title">
            <p>Medali Emas</span>
        </div>
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round5member1status === 'win')
                            <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span class="participant_name"> {!! $round5member1 !!}
                                </span>
                                <span class="log_output_32" style="background:black;">{!! $round5member1result !!}</span>
                            @else
                                <div class="participant32" style="background:white;border:1.8px solid gray">
                                    <span class="participant_name"> {!! $round5member1 !!}
                                    </span>
                                    <span class="log_output_32"
                                        style="background:gray;">{!! $round5member1result !!}</span>
                        @endif
                    </div>

                    @if ($round5member2status === 'win')
                        <div class="participant32" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span class="participant_name"> {!! $round5member2 !!}
                            </span>
                            <span class="log_output_32" style="background:black;">{!! $round5member2result !!}</span>
                        @else
                            <div class="participant32" style="background:white;border:1.8px solid gray">
                                <span class="participant_name"> {!! $round5member2 !!}
                                </span>
                                <span class="log_output_32" style="background:gray;">{!! $round5member2result !!}</span>
                    @endif
                </div>
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
