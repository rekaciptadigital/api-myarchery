<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <table style="width:100%;border: 1px solid black;">
        <thead>
            <tr>
                <th style="text-align: center;"><strong>Nama</strong></th>
                <th style="text-align: center;"><strong>Tanggal Lahir (m/d/yyyy)</strong></th>
                <th style="text-align: center;"><strong>Email</strong></th>
                <th style="text-align: center;"><strong>Gender (L/P)</strong></th>
                <th style="text-align: center;"><strong>NO HP</strong></th>
                <th style="text-align: center;"><strong>Club ID</strong></th>
                <th style="text-align: center;"><strong>Kategori ID</strong></th>
                @if ($with_contingent == 1)
                    <th style="text-align: center;"><strong>Kota ID</strong></th>
                @endif
            </tr>
        </thead>
    </table>
</body>

</html>
