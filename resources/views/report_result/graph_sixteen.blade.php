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

        .bracket .round .winners>div.matchups .matchup .participants .participant {
            box-sizing: border-box;
            color: #000000;
            background: white;
            width: 18rem;
            height: 3rem;
            box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.12);
            text-align: center;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant.winner {
            color: #1f3d7a;
            border-color: #1f3d7a;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant.loser {
            color: #dc563f;
            border-color: #dc563f;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant:not(:last-child) {
            border-bottom: thin solid #f0f2f2;
        }

        .bracket .round .winners>div.matchups .matchup .participants .participant span {
            margin: 0 1.25rem;
            line-height: 3;
            font-size: 1rem;
            font-family: 'Inter';
        }

        .bracket .round .winners>div.connector.filled .line,
        .bracket .round .winners>div.connector.filled.bottom .merger:after,
        .bracket .round .winners>div.connector.filled.top .merger:before {
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
            height: 8rem;
        }

        .bracket .round .winners>div.connector .merger:before,
        .bracket .round .winners>div.connector .merger:after {
            content: "";
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: 50%;
            border: 0 solid;
            border-color: #000000;
        }

        .bracket .round .winners>div.connector .merger:before {
            border-right-width: thin;
            border-top-width: thin;
        }

        .bracket .round .winners>div.connector .merger:after {
            border-right-width: thin;
            border-bottom-width: thin;
        }

        .bracket .round.best-16 .winners:not(:last-child) {
            margin-bottom: 2rem;
        }

        .bracket .round.best-16 .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 2rem;
        }

        .bracket .round.quarterfinals .winners:not(:last-child) {
            margin-bottom: 10rem;
        }

        .bracket .round.quarterfinals .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 10rem;
        }

        .bracket .round.quarterfinals .winners .connector .merger {
            height: 16rem;
        }

        .bracket .round.quarterfinals .winners .connector .line {
            height: 8rem;
        }

        .bracket .round.semifinals .winners .matchups .matchup:not(:last-child) {
            margin-bottom: 26rem;
        }

        .bracket .round.semifinals .winners .connector .merger {
            height: 32rem;
        }

        .bracket .round.semifinals .winners .connector .line {
            height: 16rem;
        }

        .bracket .round.finals .winners .connector .merger {
            height: 3rem;
        }

        .bracket .round.finals .winners .connector .merger:before,
        .bracket .round.finals .winners .connector .merger:after {
            border-color: transparent;
        }

        .bracket .round.finals .winners .connector .line {
            height: 1.5rem;
            border-color: transparent;
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

        .log_output {
            color: white;
            float: right;
            font-size: 10.5px;
            margin-right: 5px;
            width: 28px;
            position: relative;
            height: 25px;
            text-align: center;
            border-radius: 0.5rem;
        }
    </style>
</head>

<body translate="no">
    <div class="page" style="break-after:page">
        <!-- <img src="https://i.postimg.cc/ZRR5vW05/header.png" alt="Trulli" width="100%"> -->
        <table style="width: 100%; height: 40px;" border="0">
            <tbody>
                <tr style="height: 40px;">
                    <td style="width: 1%; height: 50px;" rowspan="2"></td>
                    <td style="width: 10%; height: 50px;" rowspan="2">
                        <img src="{{ $logo_event }}" alt="" srcset="" width="80%">
                    </td>
                    <td style="width: 10%; height: 50px;" rowspan="2">{!! $logo_archery !!}</td>
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
        <br>
        <h1 style="text-align: center">{{ $category }}</h1>
        <br>
        <div class="bracket" style="padding-left:20px">
            <section class="round best-16">
                <div class="winners">
                    <div class="matchups">
                        <div class="matchup">
                            <div class="participants">
                                @if ($round1member1status === 'win')
                                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                        <span> {!! $round1member1 !!} <p class="log_output" style="background:black;">
                                                {!! $round1member1result !!}</p>
                                        </span>
                                    @else
                                        <div class="participant" style="background:white;border:1.8px solid gray">
                                            <span> {!! $round1member1 !!} <p class="log_output"
                                                    style="background:gray;">{!! $round1member1result !!}</p>
                                            </span>
                                @endif
                            </div>
                            @if ($round1member2status === 'win')
                                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                    <span> {!! $round1member2 !!} <p class="log_output" style="background:black;">
                                            {!! $round1member2result !!}</p>
                                    </span>
                                @else
                                    <div class="participant" style="background:white;border:1.8px solid gray">
                                        <span> {!! $round1member2 !!} <p class="log_output" style="background:gray;">
                                                {!! $round1member2result !!}</p>
                                        </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="matchup">
                    <div class="participants">
                        @if ($round1member3status === 'win')
                            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span> {!! $round1member3 !!} <p class="log_output" style="background:black;">
                                        {!! $round1member3result !!}</p>
                                </span>
                            @else
                                <div class="participant" style="background:white;border:1.8px solid gray">
                                    <span> {!! $round1member3 !!} <p class="log_output" style="background:gray;">
                                            {!! $round1member3result !!}</p>
                                    </span>
                        @endif
                    </div>
                    @if ($round1member4status === 'win')
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round1member4 !!} <p class="log_output" style="background:black;">
                                    {!! $round1member4result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round1member4 !!} <p class="log_output" style="background:gray;">
                                        {!! $round1member4result !!}</p>
                                </span>
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
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round1member5 !!} <p class="log_output" style="background:black;">
                                    {!! $round1member5result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round1member5 !!} <p class="log_output" style="background:gray;">
                                        {!! $round1member5result !!}</p>
                                </span>
                    @endif
                </div>
                @if ($round1member6status === 'win')
                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span> {!! $round1member6 !!} <p class="log_output" style="background:black;">
                                {!! $round1member6result !!}</p>
                        </span>
                    @else
                        <div class="participant" style="background:white;border:1.8px solid gray">
                            <span> {!! $round1member6 !!} <p class="log_output" style="background:gray;">
                                    {!! $round1member6result !!}</p>
                            </span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member7status === 'win')
                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span> {!! $round1member7 !!} <p class="log_output" style="background:black;">
                            {!! $round1member7result !!}</p>
                    </span>
                @else
                    <div class="participant" style="background:white;border:1.8px solid gray">
                        <span> {!! $round1member7 !!} <p class="log_output" style="background:gray;">
                                {!! $round1member7result !!}</p>
                        </span>
            @endif
        </div>
        @if ($round1member8status === 'win')
            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span> {!! $round1member8 !!} <p class="log_output" style="background:black;">
                        {!! $round1member8result !!}</p>
                </span>
            @else
                <div class="participant" style="background:white;border:1.8px solid gray">
                    <span> {!! $round1member8 !!} <p class="log_output" style="background:gray;">
                            {!! $round1member8result !!}</p>
                    </span>
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
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round1member9 !!} <p class="log_output" style="background:black;">
                                    {!! $round1member9result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round1member9 !!} <p class="log_output" style="background:gray;">
                                        {!! $round1member9result !!}</p>
                                </span>
                    @endif
                </div>
                @if ($round1member10status === 'win')
                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span> {!! $round1member10 !!} <p class="log_output" style="background:black;">
                                {!! $round1member10result !!}</p>
                        </span>
                    @else
                        <div class="participant" style="background:white;border:1.8px solid gray">
                            <span> {!! $round1member10 !!} <p class="log_output" style="background:gray;">
                                    {!! $round1member10result !!}</p>
                            </span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member11status === 'win')
                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span> {!! $round1member11 !!} <p class="log_output" style="background:black;">
                            {!! $round1member11result !!}</p>
                    </span>
                @else
                    <div class="participant" style="background:white;border:1.8px solid gray">
                        <span> {!! $round1member11 !!} <p class="log_output" style="background:gray;">
                                {!! $round1member11result !!}</p>
                        </span>
            @endif
        </div>
        @if ($round1member12status === 'win')
            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span> {!! $round1member12 !!} <p class="log_output" style="background:black;">
                        {!! $round1member12result !!}</p>
                </span>
            @else
                <div class="participant" style="background:white;border:1.8px solid gray">
                    <span> {!! $round1member12 !!} <p class="log_output" style="background:gray;">
                            {!! $round1member12result !!}</p>
                    </span>
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
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round1member13 !!} <p class="log_output" style="background:black;">
                                    {!! $round1member13result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round1member13 !!} <p class="log_output" style="background:gray;">
                                        {!! $round1member13result !!}</p>
                                </span>
                    @endif
                </div>
                @if ($round1member14status === 'win')
                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span> {!! $round1member14 !!} <p class="log_output" style="background:black;">
                                {!! $round1member14result !!}</p>
                        </span>
                    @else
                        <div class="participant" style="background:white;border:1.8px solid gray">
                            <span> {!! $round1member14 !!} <p class="log_output" style="background:gray;">
                                    {!! $round1member14result !!}</p>
                            </span>
                @endif
            </div>
        </div>
    </div>
    <div class="matchup">
        <div class="participants">
            @if ($round1member15status === 'win')
                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span> {!! $round1member15 !!} <p class="log_output" style="background:black;">
                            {!! $round1member15result !!}</p>
                    </span>
                @else
                    <div class="participant" style="background:white;border:1.8px solid gray">
                        <span> {!! $round1member15 !!} <p class="log_output" style="background:gray;">
                                {!! $round1member15result !!}</p>
                        </span>
            @endif
        </div>
        @if ($round1member16status === 'win')
            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                <span> {!! $round1member16 !!} <p class="log_output" style="background:black;">
                        {!! $round1member16result !!}</p>
                </span>
            @else
                <div class="participant" style="background:white;border:1.8px solid gray">
                    <span> {!! $round1member16 !!} <p class="log_output" style="background:gray;">
                            {!! $round1member16result !!}</p>
                    </span>
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
    <section class="round quarterfinals">
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round2member1status === 'win')
                            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span> {!! $round2member1 !!} <p class="log_output" style="background:black;">
                                        {!! $round2member1result !!}</p>
                                </span>
                            @else
                                <div class="participant" style="background:white;border:1.8px solid gray">
                                    <span> {!! $round2member1 !!} <p class="log_output" style="background:gray;">
                                            {!! $round2member1result !!}</p>
                                    </span>
                        @endif
                    </div>
                    @if ($round2member2status === 'win')
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round2member2 !!} <p class="log_output" style="background:black;">
                                    {!! $round2member2result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round2member2 !!} <p class="log_output" style="background:gray;">
                                        {!! $round2member2result !!}</p>
                                </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round2member3status === 'win')
                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span> {!! $round2member3 !!} <p class="log_output" style="background:black;">
                                {!! $round2member3result !!}</p>
                        </span>
                    @else
                        <div class="participant" style="background:white;border:1.8px solid gray">
                            <span> {!! $round2member3 !!} <p class="log_output" style="background:gray;">
                                    {!! $round2member3result !!}</p>
                            </span>
                @endif
            </div>
            @if ($round2member4status === 'win')
                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span> {!! $round2member4 !!} <p class="log_output" style="background:black;">
                            {!! $round2member4result !!}</p>
                    </span>
                @else
                    <div class="participant" style="background:white;border:1.8px solid gray">
                        <span> {!! $round2member4 !!} <p class="log_output" style="background:gray;">
                                {!! $round2member4result !!}</p>
                        </span>
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
                            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span> {!! $round2member5 !!} <p class="log_output" style="background:black;">
                                        {!! $round2member5result !!}</p>
                                </span>
                            @else
                                <div class="participant" style="background:white;border:1.8px solid gray">
                                    <span> {!! $round2member5 !!} <p class="log_output" style="background:gray;">
                                            {!! $round2member5result !!}</p>
                                    </span>
                        @endif
                    </div>
                    @if ($round2member6status === 'win')
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round2member6 !!} <p class="log_output" style="background:black;">
                                    {!! $round2member6result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round2member6 !!} <p class="log_output" style="background:gray;">
                                        {!! $round2member6result !!}</p>
                                </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round2member7status === 'win')
                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span> {!! $round2member7 !!} <p class="log_output" style="background:black;">
                                {!! $round2member7result !!}</p>
                        </span>
                    @else
                        <div class="participant" style="background:white;border:1.8px solid gray">
                            <span> {!! $round2member7 !!} <p class="log_output" style="background:gray;">
                                    {!! $round2member7result !!}</p>
                            </span>
                @endif
            </div>
            @if ($round2member8status === 'win')
                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span> {!! $round2member8 !!} <p class="log_output" style="background:black;">
                            {!! $round2member8result !!}</p>
                    </span>
                @else
                    <div class="participant" style="background:white;border:1.8px solid gray">
                        <span> {!! $round2member8 !!} <p class="log_output" style="background:gray;">
                                {!! $round2member8result !!}</p>
                        </span>
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
    <section class="round semifinals">
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round3member1status === 'win')
                            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span> {!! $round3member1 !!} <p class="log_output" style="background:black;">
                                        {!! $round3member1result !!}</p>
                                </span>
                            @else
                                <div class="participant" style="background:white;border:1.8px solid gray">
                                    <span> {!! $round3member1 !!} <p class="log_output" style="background:gray;">
                                            {!! $round3member1result !!}</p>
                                    </span>
                        @endif
                    </div>
                    @if ($round3member2status === 'win')
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round3member2 !!} <p class="log_output" style="background:black;">
                                    {!! $round3member2result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round3member2 !!} <p class="log_output" style="background:gray;">
                                        {!! $round3member2result !!}</p>
                                </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="matchup">
            <div class="participants">
                @if ($round3member3status === 'win')
                    <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                        <span> {!! $round3member3 !!} <p class="log_output" style="background:black;">
                                {!! $round3member3result !!}</p>
                        </span>
                    @else
                        <div class="participant" style="background:white;border:1.8px solid gray">
                            <span> {!! $round3member3 !!} <p class="log_output" style="background:gray;">
                                    {!! $round3member3result !!}</p>
                            </span>
                @endif
            </div>
            @if ($round3member4status === 'win')
                <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                    <span> {!! $round3member4 !!} <p class="log_output" style="background:black;">
                            {!! $round3member4result !!}</p>
                    </span>
                @else
                    <div class="participant" style="background:white;border:1.8px solid gray">
                        <span> {!! $round3member4 !!} <p class="log_output" style="background:gray;">
                                {!! $round3member4result !!}</p>
                        </span>
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
    <section class="round finals">
        <div class="winners">
            <div class="matchups">
                <div class="matchup">
                    <div class="participants">
                        @if ($round4member1status === 'win')
                            <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                                <span> {!! $round4member1 !!} <p class="log_output" style="background:black;">
                                        {!! $round4member1result !!}</p>
                                </span>
                            @else
                                <div class="participant" style="background:white;border:1.8px solid gray">
                                    <span> {!! $round4member1 !!} <p class="log_output" style="background:gray;">
                                            {!! $round4member1result !!}</p>
                                    </span>
                        @endif
                    </div>
                    @if ($round4member2status === 'win')
                        <div class="participant" style="background:#D4E2FC;border:1.8px solid #0D47A1;">
                            <span> {!! $round4member2 !!} <p class="log_output" style="background:black;">
                                    {!! $round4member2result !!}</p>
                            </span>
                        @else
                            <div class="participant" style="background:white;border:1.8px solid gray">
                                <span> {!! $round4member2 !!} <p class="log_output" style="background:gray;">
                                        {!! $round4member2result !!}</p>
                                </span>
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
