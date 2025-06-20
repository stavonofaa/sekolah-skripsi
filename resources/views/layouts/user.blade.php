<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Absen App</title>
    {{-- logo company --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('bahan/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('bootstrap/bootstrap.min.css') }}">
</head>

<body>

    <div class="card rounded-bottom-5 rounded-top-0" style="background: #008080">
        <div class="card-body">
            @if (Session::has('status'))
                <div class="container">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ Session::get('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
    @yield('content2')

    <script src="{{ asset('bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
