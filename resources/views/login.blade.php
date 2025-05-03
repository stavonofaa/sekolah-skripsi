<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <style>
        .logo {
            display: block;
            margin: 0 auto 20px auto;
            max-width: 100px;
        }

        .school-name {
            text-align: center;
            font-weight: bold;
            font-size: 1.5rem;
            margin-top: 20px;
        }

        .school-subtitle {
            text-align: center;
            font-size: 1rem;
            margin-bottom: 20px;
            color: #555;
        }
    </style>
    <title>Login - SMAN 1 Cikarang Timur</title>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center mt-5">
        <div class="col-md-4 col-12 mt-5">
            <div class="card mt-5">
                <div class="card-body">
                    <img src="{{ asset('bahan/logo.png') }}" alt="Logo Sekolah" class="logo">
                    <div class="school-name">SMAN 1 CIKARANG TIMUR</div>
                    <div class="school-subtitle">Sistem Absensi Berbasis Lokasi</div>
                    <h4 class="text-center mb-4 mt-4">Login</h4>
                    <form action="/login" method="post">
                        @csrf
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required autocomplete="off">
                        <label for="Password" class="mt-2">Password</label>
                        <input type="password" name="password" id="Password" class="form-control" required>
                        <button type="submit" class="btn btn-dark mt-3 w-100">Login</button>
                    </form> 
                </div>
            </div>
        </div>
    </div>
</body>

</html>
