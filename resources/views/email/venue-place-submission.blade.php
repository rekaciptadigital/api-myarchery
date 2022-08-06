<div style="display: none; font-size: 0; line-height: 0;">
</div>
<table class="wrapper" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td class="px-sm-16" align="center" bgcolor="#EEEEEE">
                <table class="container" role="presentation" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td class="px-sm-8" align="left" bgcolor="#FFFFFF">
                                <div class="spacer line-height-sm-0 py-sm-8" style="line-height: 24px;">&zwnj;</div>
                                <table style="width: 100%;" role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tbody>
                                        <tr>
                                            <td style="width: 20%; padding-left: 30px !important;" width="10%">&nbsp;
                                            </td>
                                            <td style="width: 23%;" width="50%">
                                                <p class="text-mobile" style="text-align: right; margin-top: 5%; margin-right: -20px; font-size: 15px;">
                                                    <a style="font-size: medium;" href="https://myarchery.id" target="_blank" rel="noopener"><img class="img-sm" src="https://api-staging.myarchery.id/logo-email-archery.png" width="56" />
                                                    </a>
                                                </p>
                                            </td>
                                            <td style="width: 37%; padding-right: 30px !important;">
                                                <p class="text-mobile" style="margin-top: 5%; font-size: 15px; text-align: left;"><span style="font-size: 18pt;"><strong>MyArchery</strong></span><strong><br /></strong>
                                                </p>
                                            </td>
                                            <td style="width: 20%; padding-right: 30px !important;">
                                                <p class="text-mobile" style="text-align: right; margin-top: 5%; font-size: 15px;"><strong>&nbsp;</strong></p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<table class="wrapper" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td class="px-sm-16" align="center" bgcolor="#EEEEEE">
                <table class="container" role="presentation" width="600" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td class="px-sm-8" style="padding: 0 24px;" align="left" bgcolor="#FFFFFF">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tbody>
                                        <tr>
                                            <td class="col" style="padding: 0 8px;" align="center" width="100%">
                                                <p class="text-mode"style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 10px; text-align: justify; ">
                                                    Halo <strong>Tim RCD!</strong> <br /><br />
                                                </p>
                                                <p class="text-mode" style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: justify; ">
                                                    Berikut pengajuan Venue yang baru saja diajukan pada MyArchery, detail sebagai berikut :
                                                </p>
                                                <br />
                                                <table
                                                    style="width:100%; font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: justify;">
                                                    <tr>
                                                        <td width="20%">Nama Venue</td>
                                                        <td>:</td>
                                                        <td>{{ $data['place_name'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nama VM</td>
                                                        <td>:</td>
                                                        <td>{{ $data['vm_name'] }} </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Deskripsi</td>
                                                        <td>:</td>
                                                        <td>{{ $data['description'] }} </td>
                                                    </tr>
                                                    <tr>
                                                        <td>No. Telepon</td>
                                                        <td>:</td>
                                                        <td>{{ $data['phone_number'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Alamat</td>
                                                        <td>:</td>
                                                        <td>{{ $data['address'] }}, {{ $data['city']->name }}, {{ $data['province']->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tipe Lapangan</td>
                                                        <td>:</td>
                                                        <td>{{ $data['type'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Fasilitas</td>
                                                        <td>:</td>
                                                        <td>
                                                            @foreach ($data['facilities'] as $facility)
                                                                {{ $facility->name }},
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td>Galeri Venue</td>
                                                        <td>:</td>
                                                        <td>
                                                            <ol>
                                                            @foreach ($data['galleries'] as $gallery)
                                                                <li> <a href="{{ $gallery->file }}">{{ $gallery->file }}</a></li>
                                                            @endforeach
                                                            </ol>
                                                        </td>
                                                    </tr> -->
                                                </table>
                                                <br>
                                                <p class="text-mode" style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: justify; ">
                                                    Silahkan kunjungi <a href="{{env('APP_HOSTNAME').'enterprise/fldryepswqpxrat'}}">Dashboard Pengajuan Venue</a> untuk menyetujui permintaan ini.
                                                </p>
                                                <br />
                                                <div class="spacer" style="line-height: 55px;">&zwnj;</div>
                                                <p style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: right; ">
                                                    Terima Kasih
                                                </p>
                                                <p style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: right; ">
                                                    <a href="https://myarchery.id">MyArchery.id</a>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="spacer line-height-sm-0 py-sm-8" style="line-height: 48px;">&zwnj;</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>