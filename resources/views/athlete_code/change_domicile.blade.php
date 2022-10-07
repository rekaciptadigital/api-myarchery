<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <title>Change Domicile</title>
</head>

<body>
    <div class="container-md">
        <h1>Hello, world!</h1>
        <div class="mb-3 row">
            <label for="name" class="col-sm-1 col-form-label">Name</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="name"
                    value=": {{ $user->name }}">
            </div>
        </div>
        <div class="mb-3 row">
            <label for="email" class="col-sm-1 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="email" readonly class="form-control-plaintext" id="email"
                    value=": {{ $user->email }}">
            </div>
        </div>
        <div class="mb-3 row">
            <label for="current_province" class="col-sm-1 col-form-label">Current province</label>
            <div class="col-sm-10">
                <input type="current_province" readonly class="form-control-plaintext" id="current_province"
                    value=": {{ $province_user->name }}">
            </div>
        </div>
        <div class="mb-3 row">
            <label for="city_user" class="col-sm-1 col-form-label">Current City</label>
            <div class="col-sm-10">
                <input type="city_user" readonly class="form-control-plaintext" id="city_user"
                    value=": {{ $city_user->name }}">
            </div>
        </div>
        <div class="mb-3 row">
            <label for="address" class="col-sm-1 col-form-label">Address</label>
            <div class="col-sm-10">
                <input type="address" readonly class="form-control-plaintext" id="address"
                    value=": {{ $user->address }}">
            </div>
        </div>
        <br>
        <br>
        <br>
        <form action="/kioheswbgcgoiwagfp/{{ $user->id }}" method="post">
            <input type="hidden" name="_method" value="PUT">
            <div class="mb-3">
                <label for="province" class="form-label">New Province</label>
                <select class="form-control js-example-basic-single" name="province" id="province">
                    <option hidden>Choose Province</option>

                    @foreach ($province as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="city" class="form-label">New City</label>
                <select class="form-control js-example-basic-single" name="city" id="city">
                </select>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" name="address" id="address" cols="30" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <input type="submit" value="Simpan" class="btn btn-success">
                <input type="reset" value="Cancel" class="btn btn-danger">
            </div>
        </form>
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();

            var provinceID = $('#province').val();

            $('#province').on('change', function() {
                provinceID = $(this).val();
                if (provinceID) {
                    $.ajax({
                        url: '/api/general/get-city',
                        type: "GET",
                        data: {
                            "province_id": provinceID,
                            "limit": 30
                        },
                        dataType: "json",
                        success: function(data) {
                            if (data) {
                                $('#city').empty();
                                $('#city').append('<option hidden>Choose City</option>');
                                $.each(data.data, function(key, city) {
                                    $('select[name="city"]').append(
                                        '<option value="' + city.id + '">' + city
                                        .name + '</option>');
                                });
                            } else {
                                $('#city').empty();
                            }
                        }
                    });
                } else {
                    $('#city').empty();
                }
            });
        });
    </script>
</body>

</html>
