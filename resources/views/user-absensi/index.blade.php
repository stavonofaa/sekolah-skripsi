@extends('layouts.user')

@section('content')
    <div class="container">
        <div class="row my-3">
            <div class="col-10">
                <h3 class="text-white">{{ Auth::user()->name }}</h3>
                <span class="text-white">{{ Auth::user()->role }}</span>
            </div>
            <div class="col-2 d-flex justify-content-end">
                <div class="dropdown">
                    <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('bahan/user-avatar.png') }}" alt="" style="width: 64px; height: 64px;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/logout">Logout</a></li>
                        <li><a class="dropdown-item" href="/profile">Profile</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card bg-light rounded-3 mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <ul class="nav nav-tabs" id="absensiTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="map-tab" data-bs-toggle="tab" href="#map-absensi"
                                    role="tab" aria-controls="map-absensi" aria-selected="true">Lokasi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-black" id="camera-tab" data-bs-toggle="tab" href="#camera-absensi"
                                    role="tab" aria-controls="camera-absensi" aria-selected="false">Camera</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-6 d-flex justify-content-end">
                        <span class="mt-1 text-muted">{{ date('j F Y') }}</span>
                    </div>
                </div>

                <div class="tab-content mt-3" id="absensiTabContent">
                    <!-- TAB LOKASI -->
                    <div class="tab-pane fade show active" id="map-absensi" role="tabpanel" aria-labelledby="map-tab">
                        <div id="map" style="width: 100%; height: 400px;" class="mt-3"></div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <form id="check_in_form" action="{{ route('user.store') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="latitude" id="check_in_latitude">
                                    <input type="hidden" name="longitude" id="check_in_longitude">
                                    <input type="hidden" name="type" value="check_in">
                                    <button type="submit" class="btn btn-dark w-100 rounded-5">Absen Masuk</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <form id="check_out_form" action="{{ route('user.store') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="latitude" id="check_out_latitude">
                                    <input type="hidden" name="longitude" id="check_out_longitude">
                                    <input type="hidden" name="type" value="check_out">
                                    <button type="submit" class="btn btn-dark w-100 rounded-5">Absen Pulang</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TAB CAMERA -->
                    <div class="tab-pane fade" id="camera-absensi" role="tabpanel" aria-labelledby="camera-tab">
                        @include('user-absensi.absenCamera')
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat -->
        <div class="mt-3">
            <div class="row">
                <div class="col-6">
                    <p class="fw-bold text-white">Riwayat Absensi</p>
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="/riwayat-absen" class="fw-bold text-white text-decoration-none">Lihat semua</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('api') }}"></script>
    <script>
        let map, marker;
        let schoolLocation = null;
        let maxRadius = 100;

        function initMap() {
            // Inisialisasi peta
            map = new google.maps.Map(document.getElementById("map"), {
                center: {
                    lat: -6.274270873184602,
                    lng: 107.2014928958949
                }, // Lokasi default (Jakarta)
                zoom: 15,
            });

            // Geolocation untuk menentukan lokasi pengguna
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    console.log("Lokasi pengguna:", pos.lat, pos.lng);
                    map.setCenter(pos);

                    // Tambahkan marker
                    marker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        draggable: true, // Marker dapat digeser
                        title: "Lokasi Anda",
                        icon: {
                            url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                        }
                    });

                    // Set input latitude dan longitude
                    document.getElementById('check_in_latitude').value = pos.lat;
                    document.getElementById('check_in_longitude').value = pos.lng;
                    document.getElementById('check_out_latitude').value = pos.lat;
                    document.getElementById('check_out_longitude').value = pos.lng;

                    // Update lokasi saat marker digeser
                    marker.addListener('dragend', function() {
                        const newPos = marker.getPosition();
                        document.getElementById('check_in_latitude').value = newPos.lat();
                        document.getElementById('check_in_longitude').value = newPos.lng();
                        document.getElementById('check_out_latitude').value = newPos.lat();
                        document.getElementById('check_out_longitude').value = newPos.lng();

                        // Update informasi jarak
                        if (schoolLocation) {
                            updateDistanceInfo(newPos.lat(), newPos.lng(), schoolLocation.lat,
                                schoolLocation.lng);
                        }

                        // Jika lokasinya tersedia, gambarkan penanda dan radius sekolah
                        if (schoolLocation) {
                            drawSchoolMarkerAndRadius(schoolLocation);
                            updateDistanceInfo(pos.lat, pos.lng, schoolLocation.lat, schoolLocation.lng)
                        }
                    });
                }, function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Lokasi tidak dapat ditemukan',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Browser tidak mendukung geolokasi',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Jalankan peta
        window.onload = initMap;

        // event listener untuk absen Masuk
        document.getElementById('check_in_form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this);
        })

        // event listener untuk absen Keluar
        document.getElementById('check_out_form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this);
        })

        function submitForm(form) {
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-with': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // berhasil absen
                        let title = 'Berhasil';
                        let icon = 'success';
                        let html = data.message;

                        if (data.warning) {
                            icon = 'warning';
                            html += '<br><span class="text-warning">' + data.warning + '</span>';
                        }

                        Swal.fire({
                            title: title,
                            html: html,
                            icon: icon,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else if (data.status === 'error') {
                        // Gagal absen (diluar radius atau sudah absen)
                        Swal.fire({
                            title: 'Gagal',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem. Silakan coba lagi',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    </script>
@endsection

@section('content2')
    <div class="container mb-5">
        @foreach ($attendances as $item)
            <div class="card mt-3 bg-light rounded-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h5>{{ $item->created_at->format('j F Y') }}</h5>
                        </div>
                        <div class="col-6 text-center mt-3">
                            <h6 class="text-muted">Jam Masuk</h6>
                            <p class="text-primary fw-bold">
                                {{ $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : 'Belum Absen Masuk' }}
                            </p>
                        </div>
                        <div class="col-6 text-center mt-3">
                            <h6 class="text-muted">Jam Pulang</h6>
                            <p class="text-primary fw-bold">
                                {{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : 'Belum Absen Pulang' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
