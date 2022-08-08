<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>list pengajuan venue</title>
</head>

<body>
    <h1>List Pengajuan Venue</h1>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">No.</th>
                <th scope="col">Nama Venue</th>
                <th scope="col">Nama VM</th>
                <th scope="col">Email</th>
                <th scope="col">No. Telepon</th>
                <th scope="col">Alamat Lengkap</th>
                <th scope="col">Deskripsi</th>
                <th scope="col">Tipe Lapangan</th>
                <th scope="col">Fasilitas</th>
                <th scope="col">Galeri</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($datas as $data)
                    <tr>
                        <th scope="row">{{ $no++ }}</th>
                        <td scope="col">{{ $data->name }}</td>
                        <td scope="col">{{ $data['admin']->name }}</td>
                        <td scope="col">{{ $data['admin']->email }}</td>
                        <td scope="col">{{ $data->phone_number }}</td>
                        <td scope="col">{{ $data->address }}</td>
                        <td scope="col">{{ $data->description }}</td>
                        <td scope="col">{{ $data->place_type }}</td>
                        <td scope="col">
                            <ol>
                                @foreach ($data['facilities'] as $key => $value)
                                    <li> {{ $value['name'] }} </li>
                                @endforeach
                            </ol>
                        </td>
                        <td scope="col">
                            <ol>
                                @foreach ($data['galleries'] as $key => $value)
                                    <li> <a href="{{ $value['file'] }}" target="_blank">{{ $value['file'] }} </a> </li>
                                @endforeach
                            </ol>
                        </td>
                        <td>
                            <form action="/enterprise/fldryepswqpxrat/{{ $data->id }}" method="post">
                                <input type="hidden" name="status" value="3">
                                <input type="submit" value="Approve" class="btn btn-success">
                            </form> <br>
                            <form action="/enterprise/fldryepswqpxrat/{{ $data->id }}" method="post">
                                <input type="hidden" name="status" value="5">
                                <input type="submit" value="Reject" class="btn btn-danger">
                            </form>
                        </td>
                    </tr>
                @endforeach
        </tbody>
    </table>

    <br>
    <br>
    <br>
    <br>
    <br>

    <h1>List Pengajuan Venue yang Telah Aktif</h1>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">No.</th>
                <th scope="col">Nama Venue</th>
                <th scope="col">Nama VM</th>
                <th scope="col">Email</th>
                <th scope="col">No. Telepon</th>
                <th scope="col">Alamat Lengkap</th>
                <th scope="col">Deskripsi</th>
                <th scope="col">Tipe Lapangan</th>
                <th scope="col">Fasilitas</th>
                <th scope="col">Galeri</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($data_approved as $approved)
                <tr>
                    <th scope="row">{{ $no++ }}</th>
                    <td scope="col">{{ $approved->name }}</td>
                    <td scope="col">{{ $approved['admin']->name }}</td>
                    <td scope="col">{{ $approved['admin']->email }}</td>
                    <td scope="col">{{ $approved->phone_number }}</td>
                    <td scope="col">{{ $approved->address }}</td>
                    <td scope="col">{{ $approved->description }}</td>
                    <td scope="col">{{ $approved->place_type }}</td>
                    <td scope="col">
                        <ol>
                            @foreach ($approved['facilities'] as $key => $value)
                                <li> {{ $value['name'] }} </li>
                            @endforeach
                        </ol>
                    </td>
                    <td scope="col">
                        <ol>
                            @foreach ($approved['galleries'] as $key => $value)
                                <li> <a href="{{ $value['file'] }}" target="_blank">{{ $value['file'] }} </a> </li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
