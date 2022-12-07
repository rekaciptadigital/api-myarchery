<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <table>
        <tr>
            <th rowspan="2">Rank</th>
            <th rowspan="2">Club</th>
            <th colspan="4">indiividu</th>
            <th colspan="4">Team</th>
            <th colspan="4">Total</th>
        </tr>
        <tr>
            <th>G</th>
            <th>S</th>
            <th>B</th>
            <th>Total</th>
            <th>G</th>
            <th>S</th>
            <th>B</th>
            <th>Total</th>
            <th>G</th>
            <th>S</th>
            <th>B</th>
            <th>Total</th>
        </tr>
        @foreach ($datatables as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item['club_name'] }}</td>
                <td>
                    {{ isset($item['detail_modal_by_group']['indiividu']['gold']) ? $item['detail_modal_by_group']['indiividu']['gold'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['indiividu']['silver']) ? $item['detail_modal_by_group']['indiividu']['silver'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['indiividu']['bronze']) ? $item['detail_modal_by_group']['indiividu']['bronze'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['indiividu']['total']) ? $item['detail_modal_by_group']['indiividu']['total'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['team']['gold']) ? $item['detail_modal_by_group']['team']['gold'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['team']['silver']) ? $item['detail_modal_by_group']['team']['silver'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['team']['bronze']) ? $item['detail_modal_by_group']['team']['bronze'] : 0 }}
                </td>
                <td>
                    {{ isset($item['detail_modal_by_group']['team']['total']) ? $item['detail_modal_by_group']['team']['total'] : 0 }}
                </td>
                <td>{{ isset($item['gold']) ? $item['gold'] : 0 }}</td>
                <td>{{ isset($item['silver']) ? $item['silver'] : 0 }}</td>
                <td>{{ isset($item['bronze']) ? $item['bronze'] : 0 }}</td>
                <td>{{ isset($item['total']) ? $item['total'] : 0 }}</td>
            </tr>
        @endforeach
    </table>
</body>

</html>
