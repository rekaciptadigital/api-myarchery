<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>list pengajuan verifikasi</title>
</head>

<body>
    <h1>List Pengajuan Verifikasi</h1>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Gender</th>
                <th scope="col">Nik</th>
                <th scope="col">Alamat Lengkap</th>
                <th scope="col">Tempat Tanggal Lahir</th>
                <th scope="col">Usia</th>
                <th scope="col">Nomor Hp</th>
                <th scope="col">Provinsi Domisili</th>
                <th scope="col">Kota Domisili</th>
                <th scope="col">Nik</th>
                <th scope="col">Foto KTP/KK</th>
                <th scope="col">Selfie KTP/KK</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($data as $d)
                <tr>
                    <th scope="row">{{ $no++ }}</th>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->email }}</td>
                    <td>{{$d->gender}}</td>
                    <td>{{ $d->nik }}</td>
                    <td>{{ $d->address }}</td>
                    <td>{{$d->place_of_birth.", ".$d->date_of_birth}}</td>
                    <td>{{ $d->age }}</td>
                    <td>{{ $d->phone_number }}</td>
                    <td>{{ $d->province->name }}</td>
                    <td>{{ $d->city->name }}</td>
                    <td>{{ $d->nik }}</td>
                    <td><a href="{{ $d->ktp_kk }}">ktp_kk {{ $d->name }}</a></td>
                    <td><a href="{{ $d->selfie_ktp_kk }}">selfie_ktp_kk {{ $d->name }}</a></td>
                    <td>
                        <form action="accept" method="post" style="display: inline-block">
                            <input type="hidden" name="user_id" value="{{ $d->id }}">
                            <input type="submit" value="Accept" class="btn btn-sm btn-success">
                        </form>
                        <form action="reject" method="post" style="display: inline-block">
                            <input type="hidden" name="user_id" value="{{ $d->id }}">
                            <input type="submit" value="Reject" class="btn btn-sm btn-danger">
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

    <h1>List user yang telah terverifikasi</h1>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Kode Atlet</th>
                <th scope="col">Timestamp</th>
                <th scope="col">Email Address</th>
                <th scope="col">Nama Lengkap</th>
                <th scope="col">Gender</th>
                <th scope="col">Alamat Lengkap</th>
                <th scope="col">Tempat Tanggal Lahir</th>
                <th scope="col">Usia</th>
                <th scope="col">Nomor Hp</th>
                <th scope="col">Provinsi Domisili</th>
                <th scope="col">Kota Domisili</th>
                <th scope="col">Nik</th>
                <th scope="col">Foto KTP/KK</th>
                <th scope="col">Selfie KTP/KK</th>
                <th scope="col">Foto</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($data2 as $d2)
                <tr>
                    <th scope="row">{{ $no++ }}</th>
                    <td scope="col">{{ $d2->prefix }}</td>
                    <td scope="col">{{ $d2->date_verified }}</td>
                    <td scope="col">{{ $d2->email }}</td>
                    <td scope="col">{{ $d2->email }}</td>
                    <td scope="col">{{ $d2->gender }}</td>
                    <td scope="col">{{ $d2->address }}</td>
                    <td scope="col">{{ $d2->place_of_birth.", ".$d2->date_of_birth }}</td>
                    <td scope="col">{{ $d2->age }}</td>
                    <td scope="col">{{ $d2->phone_number }}</td>
                    <td scope="col">{{ $d2->province->name }}</td>
                    <td scope="col">{{ $d2->city->name }}</td>
                    <td scope="col">{{ $d2->nik }}</td>
                    <td><a href="{{ $d2->ktp_kk }}">ktp/kk {{ $d2->name }}</a></td>
                    <td><a href="{{ $d2->selfie_ktp_kk }}">selfie ktp/kk {{ $d2->sname }}</a></td>
                    <td><a href="{{ $d2->avatar }}">foto {{ $d2->name }}</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
