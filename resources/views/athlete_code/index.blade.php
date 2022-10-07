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
            @if ($data->count() > 0)
                @foreach ($data as $d)
                    <tr>
                        <th scope="row">{{ $no++ }}</th>
                        <td>{{ $d->name }}</td>
                        <td>{{ $d->email }}</td>
                        <td>{{ $d->gender }}</td>
                        <td>{{ $d->nik }}</td>
                        <td>{{ $d->address }}</td>
                        <td>{{ $d->place_of_birth . ', ' . $d->date_of_birth }}</td>
                        <td>{{ $d->age }}</td>
                        <td>{{ $d->phone_number }}</td>
                        <td>
                            @if ($d->province)
                                {{ $d->province->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($d->city)
                                {{ $d->city->name . ' (' . $d->city->ktp_id . '[' . $d->city->prefix . '])' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $d->nik }}</td>
                        <td>
                            @if ($d->ktp_kk)
                                <a href="{{ $d->ktp_kk }}" target="_blank">ktp_kk {{ $d->name }}</a>
                            @else
                                null
                            @endif
                        </td>
                        <td>
                            @if ($d->selfie_ktp_kk)
                                <a href="{{ $d->selfie_ktp_kk }}" target="_blank">selfie_ktp_kk
                                    {{ $d->name }}</a>
                            @else
                                null
                            @endif
                        </td>
                        <td>
                            @if (!empty($d->city->prefix) && !empty($d->city->ktp_id))
                                <form action="accept" method="post" style="display: inline-block">
                                    <input type="hidden" name="user_id" value="{{ $d->id }}">
                                    <input type="submit" value="Accept" class="btn btn-sm btn-success">
                                </form>
                            @endif
                            <form action="reject" method="post" style="display: inline-block">
                                <input type="hidden" name="user_id" value="{{ $d->id }}">
                                <input type="submit" value="Reject" class="btn btn-sm btn-danger">
                                </br><label>Reason reject</label>
                                <textarea name="reason"></textarea>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <h1>data kosong</h1>
            @endif
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
            @if ($data2->count() > 0)
                @foreach ($data2 as $d2)
                    <tr>
                        <th scope="row">{{ $no++ }}</th>
                        <td scope="col">{{ $d2->prefix }}</td>
                        <td scope="col">{{ $d2->date_verified }}</td>
                        <td scope="col">{{ $d2->email }}</td>
                        <td scope="col">{{ $d2->name }}</td>
                        <td scope="col">{{ $d2->gender }}</td>
                        <td scope="col">{{ $d2->address }}</td>
                        <td scope="col">{{ $d2->place_of_birth . ', ' . $d2->date_of_birth }}</td>
                        <td scope="col">{{ $d2->age }}</td>
                        <td scope="col">{{ $d2->phone_number }}</td>
                        <td scope="col">
                            @if ($d2->province)
                                {{ $d2->province->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td scope="col">
                            @if ($d2->city)
                                {{ $d2->city->name . ' (' . $d2->city->id . ' [' . $d2->city->prefix . '])' }}
                            @else
                                -
                            @endif
                        </td>
                        <td scope="col">{{ $d2->nik }}</td>
                        <td>
                            @if ($d2->ktp_kk)
                                <a href="{{ $d2->ktp_kk }}" target="_blank">ktp/kk {{ $d2->name }}</a>
                            @else
                                null
                            @endif
                        </td>
                        <td>
                            @if ($d2->selfie_ktp_kk)
                                <a href="{{ $d2->selfie_ktp_kk }}" target="_blank">selfie
                                    ktp/kk{{ $d2->sname }}</a>
                            @else
                                null
                            @endif
                        </td>
                        <td>
                            @if ($d2->avatar)
                                <a href="{{ $d2->avatar }}" target="_blank">foto {{ $d2->name }}</a>
                            @else
                                null
                            @endif
                        </td>
                        <td>
                            <form action="/kioheswbgcgoiwagfp/{{ $d2->id }}" method="get">
                                <input type="submit" value="Change Domisili" class="btn btn-warning">
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <h1>data kosong</h1>
            @endif
        </tbody>
    </table>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
