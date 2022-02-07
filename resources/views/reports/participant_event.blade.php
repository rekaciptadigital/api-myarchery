<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>DAFTAR PARTISIPAN </title>
</head>

<body>

    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 13; color: #000000; font-weight: bold; white-space: pre-line">
                    <strong>DAFTAR PARTISIPAN {{$event_name}}</strong></td>
    </table>
    <table style="width: 100%; height: 70px;" border="0">
    <td colspan="9"
                    style="text-align: left; font-size: 12; color: #000000; white-space: pre-line">
                    
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
                <th style="text-align: center; background: #ffd68a;"><strong>ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Event ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>User ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Name</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Type</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Email</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Phone Number</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Age</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Gender</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Team Category ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Age Category ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Competition Category ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Distance Category ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Qualificication Date</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Transaction Log ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Unique ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Team Name</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Event Category ID</strong></th>
                <th style="text-align: center; background: #ffd68a;"><strong>Club ID</strong></th>
            </tr>
            @foreach ($datas as $data)
            <tr>
                <td style="text-align: center;">{{ $data['id'] ? $data['id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['event_id'] ? $data['event_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['user_id'] ? $data['user_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['name'] ? $data['name'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['type'] ? $data['type'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['email'] ? $data['email'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['phone_number'] ? $data['phone_number'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['age'] ? $data['age'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['gender'] ? $data['gender'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['team_category_id'] ? $data['team_category_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['age_category_id'] ? $data['age_category_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['competition_category_id'] ? $data['competition_category_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['distance_id'] ? $data['distance_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['qualification_date'] ? $data['qualification_date'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['transaction_log_id'] ? $data['transaction_log_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['unique_id'] ? $data['unique_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['team_name'] ? $data['team_name'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['event_category_id'] ? $data['event_category_id'] : '-' }}</td>
                <td style="text-align: center;">{{ $data['club_id'] ? $data['club_id'] : '-' }}</td>

            </tr>
            @endforeach
            <!-- <tr>
            <td colspan="3"></td>
        </tr> -->
        </tbody>
    </table>
</body>

</html>
