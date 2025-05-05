@extends('layouts.app')

@section('styles')
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
@endsection

@section('content')
    <div class="container d-flex justify-content-center align-items-center mt-5">
        <div class="col-md-4 col-12 mt-5">
            <div class="card mt-5">
                <div class="card-body">
                    <img src="{{ asset('bahan/logo.png') }}" alt="Logo Sekolah" class="logo">
                    <div class="school-name">SMAN 1 CIKARANG TIMUR</div>
                    <div class="school-subtitle">Sistem Absensi Berbasis Lokasi</div>
                    <h4 class="text-center mb-4 mt-4">Register</h4>

                    <form class="needs-validation" action="{{ route('register') }}" method="post"
                        enctype="multipart/form-data" novalidate id="registerForm">
                        {{ csrf_field() }}
                        <div id="email">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required
                                autocomplete="off">
                            <div class="invalid-feedback">
                                Email wajib diisi
                            </div>
                        </div>

                        <div id="userName">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control mt-2" required
                                autocomplete="off">
                            <div class="invalid-feedback">
                                Username wajib diisi
                            </div>
                        </div>

                        <div class="passWord">
                            <label for="Password">Password</label>
                            <input type="password" name="password" id="Password" class="form-control mt-2" required>
                            <div class="invalid-feedback">
                                Password wajib diisi
                            </div>
                        </div>

                        <div id="role">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-select mt-2" required>
                                <option value="" disabled selected>Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                            <div class="invalid-feedback">
                                Role wajib dipilih
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark mt-3 w-100">Register</button>
                    </form>

                    <div class="text-center mt-3">
                        <span>Sudah punya akun?</span>
                        <a href="{{ route('login') }}">Login sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formRegister = document.getElementById('registerForm')

            if (formRegister) {
                formRegister.addEventListener('submit', function(e) {
                    if (!formRegister.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();

                        Swal.fire({
                            icon: 'error',
                            title: 'Form tidak valid',
                            text: 'Harap periksa dan lengkapi semua data',
                            timer: 2000,
                            showConfirmButton: false
                        })
                    } else {
                        // show loading
                        Swal.fire({
                            title: 'Memproses...',
                            html: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })
                    }

                    formRegister.classList.add('was-validated');
                });
            }

            // Check for validation errors
            @if ($errors->any())
                const errorMessages = [];
                @foreach ($errors->all() as $error)
                    errorMessages.push("{{ $error }}");
                @endforeach

                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi gagal',
                    html: errorMessages.join('<br>'),
                    confirmButtonText: 'Coba lagi'
                });
            @endif

            // Check for success message
            @if (session('status') && session('redirect_url'))
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi berhasil!',
                    text: '{{ session('status') }}',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ session('redirect_url') }}';
                });
            @endif
        });
    </script>
@endsection
