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
                    <h4 class="text-center mb-4 mt-4">Login</h4>

                    <form class="needs-validation" action="/login" method="post" novalidate>
                        @csrf
                        <!-- Google Sign In -->
                        <a href="{{ url('auth/google') }}"
                            class="btn btn-light w-100 mb-3 d-flex align-items-center justify-content-center gap-2">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo"
                                width="20" height="20">
                            Login Akun Google Anda
                        </a>

                        <div id="userName">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required
                                autocomplete="off">
                            <div class="invalid-feedback">
                                Username wajib diisi
                            </div>
                        </div>

                        <div class="passWord">
                            <label for="Password" class="mt-2">Password</label>
                            <input type="password" name="password" id="Password" class="form-control" required>
                            <div class="invalid-feedback">
                                Password wajib diisi
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark mt-3 w-100">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <span>Belum punya akun?</span>
                        <a href="{{ route('register') }}">Daftar sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Periksa flash messages
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Login gagal',
                    text: '{{ session('error') }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            @if (session('status'))
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "{{ session('status') }}",
                    toast: true,
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: {
                        popup: 'mt-6'
                    }
                });
            @endif

            // Form validation
            const formLogin = document.querySelector('.needs-validation');

            if (formLogin) {
                formLogin.addEventListener('submit', function(e) {
                    if (!formLogin.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        // Show loading
                        Swal.fire({
                            title: 'Memproses...',
                            html: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }

                    formLogin.classList.add('was-validated');
                });
            }
        });
    </script>
@endsection
