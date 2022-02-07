<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>LAPORAN JALAN PROVINSI KAB KOTA</title>
</head>

<body>
    <table style="width: 100%; height: 70px;" border="0">
        <tbody>
            <tr style="height: 70px;">
                <td style="width: 13.2422%; height: 70px;" rowspan="2">&nbsp;</td>
                <td style="width: 13.2422%; height: 70px;" rowspan="2">&nbsp;</td>
                <td colspan="8" style="width: 81.0696%; height: 50px; font-size: 14;">
                    <p style="text-align: left;">
                        <span><strong>&nbsp;KEMENTERIAN PEKERJAAN UMUM
                                DAN PERUMAHAN RAKYAT<br />&nbsp;DIREKTORAT JENDERAL BINA MARGA</strong></span></p>
                </td>
            </tr>
            <tr style="height: 70px;">
                <td colspan="8" style="width: 81.0696%; height: 20px; font-size: 11;">
                    <p style="text-align: left;"><span
                            >&nbsp;&nbsp;APLIKASI JAKI 2.0</span></p>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">
                    <strong>LAPORAN JALAN PROVINSI/KABUPATEN/KOTA</strong></td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    <p>{{ $range_time }}</p></td>
    </table>
    <table border="0">
        <td style="text-align: left; color: #000000; white-space: pre-line">
        </td>
    </table>

    <table>
        <thead>
            <!-- <tr>
                <th>Table Heading</th>
            </tr> -->
        </thead>
        <tbody>
            <tr>
                <th style="text-align: center; background: #ffd68a;"><strong>ID Laporan</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Pelapor</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Pembuat Laporan</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Jenis Kerusakan</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Tujuan</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Catatan</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Koordinat</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Detail Lokasi</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Tanggal</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Status Laporan</strong></th>
            </tr>
            @foreach ($datas as $data)
            <tr>
                <td style="text-align: center;">{{ $data['id_laporan'] ? $data['id_laporan'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['name'] ? $data['name'] :'-' }}</td>
                <td style="text-align: center;">{{ $data['pembuat_laporan'] ? $data['pembuat_laporan'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['kerusakan'] ? $data['kerusakan'] :'-'  }}</td>
                <td style="text-align: center;">{{ '-' }}</td>
                <td style="text-align: center;">{{ $data['catatan'] ? $data['catatan'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['koordinat'] ? $data['koordinat'] : '-'  }}</td>
                <td style="text-align: center;">{{ $data['alamat'] ? $data['alamat'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['tanggal'] ? $data['tanggal'] :'-' }}</td>
                <td style="text-align: center;">{{ $data['status']  ? $data['status'] :'-' }}</td>
            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
