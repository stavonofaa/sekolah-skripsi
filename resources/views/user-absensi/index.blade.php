@extends('layouts.user')

@section('content')
    <div class="container">
        <div class="row my-3">
            <div class="col-10">
                <h3 class="text-white">{{ Auth::user()->name }}</h3>
                <span class="text-white">{{ Auth::user()->jabatan }}</span>
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
                        <h5 class="fw-bold">Absensi App</h5>
                    </div>
                    <div class="col-6 d-flex justify-content-end">
                        <span class="mt-1 text-muted">{{ date('j F Y') }}</span>
                    </div>
                </div>

                <div id="map" style="width: 100%; height: 400px;" class="mt-3"></div>

                {{-- Form Absen Masuk dan Pulang --}}
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
        </div>

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

        function initMap() {
            // Inisialisasi peta
            map = new google.maps.Map(document.getElementById("map"), {
                center: {
                    lat: -6.200000,
                    lng: 106.816666
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
                    map.setCenter(pos);

                    // Tambahkan marker
                    marker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        draggable: true, // Marker dapat digeser
                    });

                    // Set input latitude dan longitude
                    document.getElementById('check_in_latitude').value = pos.lat;
                    document.getElementById('check_in_longitude').value = pos.lng;

                    // Update lokasi saat marker digeser
                    marker.addListener('dragend', function() {
                        const newPos = marker.getPosition();
                        document.getElementById('check_in_latitude').value = newPos.lat();
                        document.getElementById('check_in_longitude').value = newPos.lng();
                    });
                }, function() {
                    alert("Lokasi tidak dapat ditemukan.");
                });
            } else {
                alert("Browser tidak mendukung geolokasi.");
            }
        }

        // Jalankan peta
        window.onload = initMap;

        // Event listener untuk absen keluar
        document.getElementById('check_out_form').addEventListener('submit', function(event) {
            event.preventDefault();
            const pos = marker.getPosition();
            document.getElementById('check_out_latitude').value = pos.lat();
            document.getElementById('check_out_longitude').value = pos.lng();
            event.target.submit();
        });
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
