<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        .block {
            display: block;
            width: 40%;
            border: none;
            background-color: #0D47A1;
            padding: 14px 28px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
        }

        /* .block:hover {
            background-color: #ddd;
            color: black;
        } */
    </style>

</head>

<body>
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
                                    <table style="width: 100%;" role="presentation" width="100%" cellspacing="0"
                                        cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td style="width: 20%; padding-left: 30px !important;" width="10%">
                                                    &nbsp;
                                                </td>
                                                <td style="width: 23%;" width="50%">
                                                    <p class="text-mobile"
                                                        style="text-align: right; margin-top: 5%; margin-right: -20px; font-size: 15px;">
                                                        <a style="font-size: medium;" href="https://myarchery.id"
                                                            target="_blank" rel="noopener"><img class="img-sm"
                                                                src="https://api-staging.myarchery.id/logo-email-archery.png"
                                                                width="56" /></a>
                                                    </p>
                                                </td>
                                                <td style="width: 37%; padding-right: 30px !important;">
                                                    <p class="text-mobile"
                                                        style="margin-top: 5%; font-size: 15px; text-align: left;"><span
                                                            style="font-size: 18pt;"><strong>MyArchery</strong></span><strong><br /></strong>
                                                    </p>
                                                </td>
                                                <td style="width: 20%; padding-right: 30px !important;">
                                                    <p class="text-mobile"
                                                        style="text-align: right; margin-top: 5%; font-size: 15px;">
                                                        <strong>&nbsp;</strong>
                                                    </p>
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
                                                <td class="col" style="padding: 0 8px;" align="center"
                                                    width="100%">
                                                    <p class="text-mode"
                                                        style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 10px; text-align: justify; margin: 20px 70px;">
                                                        Halo <strong>{{ $data['name'] }}</strong>, <br /><br /></p>
                                                    <p class="text-mode"
                                                        style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: justify; margin: 20px 70px;">
                                                        Anda telah didaftarkan sebagai pengolah data pada event
                                                        {{ $data['event_name'] }}. Berikut email dan password untuk akun
                                                        anda untuk diakses pada website MyArchery Organizer.
                                                    </p>
                                                    <br />
                                                    <p>Email: {{ $data['email'] }}</p>
                                                    <p>Email: {{ $data['password'] }}</p>
                                                    <br />
                                                    <a href="https://myarchery.id/home" target="_blank"
                                                        rel="noopener noreferrer" class="block" style="color: white;">
                                                        Buka MyArchery
                                                    </a>
                                                    <div class="spacer" style="line-height: 55px;">&zwnj;</div>
                                                    <p
                                                        style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: right; margin: 20px 70px;">
                                                        Terima Kasih</p>
                                                    <p
                                                        style="font-family: 'Open Sans', sans-serif, Verdana; font-size: 15px; color: #4c4c4c; line-height: 18px; text-align: right; margin: 20px 70px;">
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
</body>

</html>
