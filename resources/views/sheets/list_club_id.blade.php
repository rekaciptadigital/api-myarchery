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
                <th style="text-align: center;"><strong>ID</strong></th>
                <th style="text-align: center;"><strong>Club Name</strong></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td style="text-align:center">{{ $d->id }}</td>
                    <td style="text-align:center">{{ $d->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
