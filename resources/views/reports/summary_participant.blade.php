<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>RINGKASAN PESERTA</title>


    <style>
        table,
        th,
        td {
            border: thin solid black !important;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9"
            style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">

            <strong>Ringkasan Peserta EVENT {{ $event->event_name }}</strong>
        </td>

    </table>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9" style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
            <p>Mulai dari tanggal {{ $event->event_start_datetime }} - {{ $event->event_end_datetime }}</p>
        </td>
    </table>
    <table border="4" style="border:thin solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9" style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
            <p>Berdasarkan Jenis Regu</p>
        </td>
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th rowspan="2" style="text-align: center; background: #a1a11a;"><strong>No</strong></th>
                <th rowspan="2" style="text-align: center; background: #FFFF00;"><strong>Kategori</strong></th>
                <th rowspan="2" style="text-align: center; background: #a1a11a;"><strong>Total Quota</strong></th>
                <th colspan="2" style="text-align: center; background: #FFFF00;"><strong>Registrasi Normal</strong>
                </th>
                <th colspan="2" style="text-align: center; background: #a1a11a;"><strong>Registrasi Early
                        Bird</strong></th>
                <th rowspan="2" style="text-align: center; background: #FFFF00;"><strong>Sisa Kuota</strong></th>
                <th rowspan="2" style="text-align: center; background: #a1a11a;"><strong>Total Uang Masuk</strong>
                </th>

            </tr>

            <tr>
                <th style="text-align: center; background: #FFFF00;"><strong>Harga</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Terjual</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Harga</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Total Terjual</strong></th>
            </tr>
            <?php
            $no = 0;
            $total_amount = 0;
            $left_quota = 0;
            $quota = 0;
            $quota_sell = 0;
            $quota_sell_early_bird = 0;
            ?>
            @foreach ($team_category as $data)
                <tr>
                    <td style="text-align: center;">{{ $no = $no + 1 }}</td>
                    <td style="text-align: left;">{{ $data['label'] ? $data['label'] : '-' }}</td>
                    <td style="text-align: center;">{{ $data['quota'] ? $data['quota'] : '0' }}</td>
                    <td style="text-align: right;">Rp{{ $data['fee'] ? number_format($data['fee']) : '0' }}</td>
                    <td style="text-align: center;">{{ $data['total_sell'] ? $data['total_sell'] : '0' }}</td>
                    <td style="text-align: right;">
                        Rp{{ $data['fee_early_bird'] ? number_format($data['fee_early_bird']) : '0' }}</td>
                    <td style="text-align: center;">
                        {{ $data['total_sell_early_bird'] ? $data['total_sell_early_bird'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['left_quota'] ? $data['left_quota'] : '0' }}</td>
                    <td style="text-align: right;">
                        Rp{{ $data['total_amount'] ? number_format($data['total_amount']) : '0' }}</td>
                    <?php
                    $total_amount = $data['total_amount'] + $total_amount;
                    $left_quota = $left_quota + $data['left_quota'];
                    $quota = $data['quota'] + $quota;
                    $quota_sell = $data['total_sell'] + $quota_sell;
                    $quota_sell_early_bird = $data['total_sell_early_bird'] + $quota_sell_early_bird;
                    ?>
                </tr>
            @endforeach

            <tr>
                <td colspan="2" style="text-align: center;background: #ccccad;"><strong>Total</strong></td>
                <td style="text-align: center;background: #ccccad;">{{ $quota }}</td>
                <td style="text-align: center;background: #ccccad;"></td>
                <td style="text-align: center;background: #ccccad;">{{ $quota_sell }}</td>
                <td style="text-align: center;background: #ccccad;"></td>
                <td style="text-align: center;background: #ccccad;">{{ $quota_sell_early_bird }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $left_quota }}</td>
                <td style="text-align: right;background: #ccccad;">Rp{{ number_format($total_amount) }}</td>
            </tr>
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>

    <table border="4" style="border:thin solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9" style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
            <p>Berdasarkan Regu</p>
        </td>
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th style="text-align: center; background: #a1a11a;"><strong>No</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>NAMA REGU</strong></th>
                {{-- <th style="text-align: center; background: #a1a11a;"><strong>HARGA DAFTAR</strong></th> --}}
                <th style="text-align: center; background: #FFFF00;"><strong>TOTAL KUOTA</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>TOTAL PENDAFTAR</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>SISA KUOTA</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>TOTAL UANG MASUK</strong></th>

            </tr>
            <?php
            $no = 0;
            $total_amount = 0;
            $left_quota = 0;
            $quota = 0;
            $quota_sell = 0;
            ?>
            @foreach ($team as $key => $data)
                <tr>
                    <td style="text-align: center;">{{ $no = $no + 1 }}</td>
                    <td style="text-align: left;">{{ $key }}</td>
                    {{-- <td style="text-align: right;">Rp{{ $data['amount'] ? number_format($data['amount']) : '0' }}</td> --}}
                    <td style="text-align: center;">{{ $data['quota'] ? $data['quota'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['quota_sell'] ? $data['quota_sell'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['left_quota'] ? $data['left_quota'] : '0' }}</td>
                    <td style="text-align: right;">
                        Rp{{ $data['total_amount'] ? number_format($data['total_amount']) : '0' }}</td>
                </tr>
                <?php
                $total_amount = $data['total_amount'] + $total_amount;
                $left_quota = $left_quota + $data['left_quota'];
                $quota = $data['quota'] + $quota;
                $quota_sell = $data['quota_sell'] + $quota_sell;
                ?>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: center;background: #ccccad;"><strong>Total</strong></td>
                <td style="text-align: center;background: #ccccad;">{{ $quota }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $quota_sell }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $left_quota }}</td>
                <td style="text-align: right;background: #ccccad;">Rp{{ number_format($total_amount) }}</td>
            </tr>
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
    <table border="4" style="border:thin solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9" style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
            <p>Berdasarkan Nomor Pertandingan</p>
        </td>
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <?php
            $no = 0;
            $total_amount = 0;
            $q_i = 0;
            $q_t = 0;
            $q_m = 0;
            $ts_i = 0;
            $ts_t = 0;
            $ts_m = 0;
            $rq_i = 0;
            $rq_t = 0;
            $rq_m = 0;
            ?>
            <tr>
                <th rowspan="2" style="text-align: center; background: #a1a11a;"><strong>No</strong></th>
                <th rowspan="2" style="text-align: center; background: #FFFF00;"><strong>Kategori</strong></th>
                {{-- <th colspan="3" style="text-align: center; background: #a1a11a;"><strong>Biaya</strong></th> --}}
                <th colspan="3" style="text-align: center; background: #FFFF00;"><strong>Kuota</strong></th>
                <th colspan="3" style="text-align: center; background: #a1a11a;"><strong>Terjual</strong></th>
                <th colspan="3" style="text-align: center; background: #FFFF00;"><strong>Sisa kuota</strong></th>
                <th rowspan="2" style="text-align: center; background: #a1a11a;"><strong>Total Uang Masuk</strong>
                </th>
            </tr>

            <tr>
                {{-- <th style="text-align: center; background: #a1a11a;"><strong>Individu Putra/Putri</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Beregu Putra/Putri</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Beregu Campuran</strong></th> --}}
                <th style="text-align: center; background: #FFFF00;"><strong>Individu Putra/Putri</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Beregu Putra/Putri</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Beregu Campuran</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Individu Putra/Putri</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Beregu Putra/Putri</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Beregu Campuran</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Individu Putra/Putri</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Beregu Putra/Putri</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Beregu Campuran</strong></th>
            </tr>

            @foreach ($competition_category as $key => $data)
                <tr>
                    <td style="text-align: center;">{{ $no = $no + 1 }}</td>
                    <td style="text-align: left;">{{ $data['label'] }}</td>
                    {{-- <td style="text-align: right;">
                        Rp{{ $data['fee'] ? number_format($data['fee']['individu']) : '0' }}
                    </td>
                    <td style="text-align: right;">
                        Rp{{ $data['fee'] ? number_format($data['fee']['team']) : '0' }}
                    </td>
                    <td style="text-align: right;">
                        Rp{{ $data['fee'] ? number_format($data['fee']['mix_team']) : '0' }}
                    </td> --}}
                    <td style="text-align: center;">
                        {{ $data['quota'] ? $data['quota']['individu'] : '0' }}
                    </td>
                    <td style="text-align: center;">{{ $data['quota'] ? $data['quota']['team'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['quota'] ? $data['quota']['mix_team'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['total_sell'] ? $data['total_sell']['individu'] : '0' }}
                    </td>
                    <td style="text-align: center;">{{ $data['total_sell'] ? $data['total_sell']['team'] : '0' }}</td>
                    <td style="text-align: center;">{{ $data['total_sell'] ? $data['total_sell']['mix_team'] : '0' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $data['remaining_quota'] ? $data['remaining_quota']['individu'] : '0' }}</td>
                    <td style="text-align: center;">
                        {{ $data['remaining_quota'] ? $data['remaining_quota']['team'] : '0' }}</td>
                    <td style="text-align: center;">
                        {{ $data['remaining_quota'] ? $data['remaining_quota']['mix_team'] : '0' }}</td>
                    <td style="text-align: right;">
                        Rp{{ $data['total_amount'] ? number_format($data['total_amount']) : '0' }}</td>
                </tr>
                <?php
                $q_i = $data['quota']['individu'] + $q_i;
                $q_t = $data['quota']['team'] + $q_t;
                $q_m = $data['quota']['mix_team'] + $q_m;
                $ts_i = $data['total_sell']['individu'] + $ts_i;
                $ts_t = $data['total_sell']['team'] + $ts_t;
                $ts_m = $data['total_sell']['mix_team'] + $ts_m;
                $rq_i = $data['remaining_quota']['individu'] + $rq_i;
                $rq_t = $data['remaining_quota']['team'] + $rq_t;
                $rq_m = $data['remaining_quota']['mix_team'] + $rq_m;
                $total_amount = $data['total_amount'] + $total_amount;
                ?>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: center;background: #ccccad;"><strong>Total</strong></td>
                <td style="text-align: center;background: #ccccad;">{{ $q_i }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $q_t }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $q_m }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $ts_i }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $ts_t }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $ts_m }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $rq_i }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $rq_t }}</td>
                <td style="text-align: center;background: #ccccad;">{{ $rq_m }}</td>
                <td style="text-align: right;background: #ccccad;">Rp{{ number_format($total_amount) }}</td>
            </tr>
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
    <table border="4" style="border:thin solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9" style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
            <p>Berdasarkan Jenis Kelamin</p>
        </td>
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th style="text-align: center; background: #a1a11a;"><strong>No</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Jenis Kelamin</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Total Pendaftar</strong></th>
            </tr>
            <?php
            $no = 0;
            $total = 0;
            ?>
            @foreach ($gender as $key => $data)
                <tr>
                    <td style="text-align: center;">{{ $no = $no + 1 }}</td>
                    <td style="text-align: center;">{{ $key }}</td>
                    <td style="text-align: center;">
                        {{ $data['total_participant'] ? $data['total_participant'] : '0' }}</td>
                </tr>
                <?php
                $total = $total + $data['total_participant'];
                ?>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: center;"><strong>Total</strong></td>
                <td style="text-align: center;">{{ $total }}</td>
            </tr>
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
    <table border="4" style="border:thin solid #000">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
        <td colspan="9" style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
            <p>Berdasarkan Ringkasan Umum</p>
        </td>
    </table>


    <table style="width:100%;border: 1px solid black;">
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th rowspan="2" style="text-align: center; background: #FFFF00;"><strong>No</strong></th>
                <th rowspan="2" style="text-align: center; background: #a1a11a;"><strong>Kategori</strong></th>
                <th colspan="3" style="text-align: center; background: #FFFF00;"><strong>Individu Putra</strong>
                </th>
                <th colspan="3" style="text-align: center; background: #a1a11a;"><strong>Individu Putri</strong>
                </th>
                <th colspan="3" style="text-align: center; background: #FFFF00;"><strong>Beregu Putra</strong></th>
                <th colspan="3" style="text-align: center; background: #a1a11a;"><strong>Beregu Putri</strong></th>
                <th colspan="3" style="text-align: center; background: #FFFF00;"><strong>Beregu Campuran</strong>
                </th>
            </tr>
            <?php
            $no = 0;
            $m_s = 0;
            $m_q = 0;
            $m_l = 0;
            $f_s = 0;
            $f_q = 0;
            $f_l = 0;
            $bm_s = 0;
            $bm_q = 0;
            $bm_l = 0;
            $bf_s = 0;
            $bf_q = 0;
            $mt_l = 0;
            $mt_s = 0;
            $mt_q = 0;
            $mt_l = 0;
            ?>
            <tr>
                <th style="text-align: center; background: #FFFF00;"><strong>Terisi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Kuota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Sisa Kuota</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Terisi</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Total Kuota</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Sisa Kuota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Terisi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Kuota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Sisa Kuota</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Terisi</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Total Kuota</strong></th>
                <th style="text-align: center; background: #a1a11a;"><strong>Sisa Kuota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Terisi</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Total Kuota</strong></th>
                <th style="text-align: center; background: #FFFF00;"><strong>Sisa Kuota</strong></th>
            </tr>

            @foreach ($public_summary as $key => $data)
                <tr>
                    <td style="text-align: center;">{{ $no = $no + 1 }}</td>
                    <td style="text-align: right;">{{ $data['label'] ? $data['label'] : '' }}</td>
                    <td style="text-align: right;">{{ $data['individu_male']['sell'] }}</td>
                    <td style="text-align: right;">{{ $data['individu_male']['quota'] }}</td>
                    <td style="text-align: right;">{{ $data['individu_male']['left'] }}</td>
                    <td style="text-align: right;">{{ $data['individu_female']['sell'] }}</td>
                    <td style="text-align: right;">{{ $data['individu_female']['quota'] }}</td>
                    <td style="text-align: right;">{{ $data['individu_female']['left'] }}</td>
                    <td style="text-align: right;">{{ $data['male_team']['sell'] }}</td>
                    <td style="text-align: right;">{{ $data['male_team']['quota'] }}</td>
                    <td style="text-align: right;">{{ $data['male_team']['left'] }}</td>
                    <td style="text-align: right;">{{ $data['female_team']['sell'] }}</td>
                    <td style="text-align: right;">{{ $data['female_team']['quota'] }}</td>
                    <td style="text-align: right;">{{ $data['female_team']['left'] }}</td>
                    <td style="text-align: right;">{{ $data['mix_team']['sell'] }}</td>
                    <td style="text-align: right;">{{ $data['mix_team']['quota'] }}</td>
                    <td style="text-align: right;">{{ $data['mix_team']['left'] }}</td>
                </tr>
                <?php
                $m_s = $data['individu_male']['sell'] + $m_s;
                $m_q = $data['individu_male']['quota'] + $m_q;
                $m_l = $data['individu_male']['left'] + $m_l;
                $f_s = $data['individu_female']['sell'] + $f_s;
                $f_q = $data['individu_female']['quota'] + $f_q;
                $f_l = $data['individu_female']['left'] + $f_l;
                $bm_s = $data['male_team']['sell'] + $bm_s;
                $bm_q = $data['male_team']['quota'] + $bm_q;
                $bm_l = $data['male_team']['left'] + $bm_l;
                $bf_s = $data['female_team']['sell'] + $bf_s;
                $bf_q = $data['female_team']['quota'] + $bf_q;
                $mt_l = $data['female_team']['left'] + $mt_l;
                $mt_s = $data['mix_team']['sell'] + $mt_s;
                $mt_q = $data['mix_team']['quota'] + $mt_q;
                $mt_l = $data['mix_team']['left'] + $mt_l;
                ?>
            @endforeach

            <tr>
                <td colspan="2" style="text-align: center;background: #ccccad;"><strong>Total</strong></td>
                <td style="text-align: right;background: #ccccad;">{{ $m_s }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $m_q }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $m_l }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $f_s }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $f_q }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $f_l }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $bm_s }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $bm_q }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $bm_l }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $bf_s }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $bf_q }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $mt_l }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $mt_s }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $mt_q }}</td>
                <td style="text-align: right;background: #ccccad;">{{ $mt_l }}</td>
            </tr>
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>

</body>

</html>
