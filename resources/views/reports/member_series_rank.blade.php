<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>City</th>
            <th>Point</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['list_member_point'] as $d)
            <tr>
                <td>{{ $d['user']['name'] }}</td>
                <td>{{ $d['user']['city'] }}</td>
                <td>{{ $d['total_point'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
