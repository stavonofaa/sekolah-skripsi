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
                    lat: -6.209852876146287,
                    lng: 106.99214995145515
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

        // Inisialisasi koordinat
        let latitude = null;
        let longitude = null;

        // Ambil lokasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            getCurrentLocation()
                .then(() => console.log('Initial location obtained successfully'))
                .catch(error => {
                    console.error('Failed to get initial location:', error);
                    Swal.fire({
                        title: 'Peringatan',
                        text: 'Gagal mendapatkan lokasi. Pastikan GPS aktif dan izinkan akses lokasi.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                });
        });

        // Ambil lokasi GPS
        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    return reject(new Error('Geolocation tidak didukung oleh browser ini'));
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latitude = position.coords.latitude;
                        longitude = position.coords.longitude;
                        console.log('Location obtained:', {
                            latitude,
                            longitude
                        });
                        resolve({
                            latitude,
                            longitude
                        });
                    },
                    (error) => {
                        console.error('Geolocation error:', error);
                        reject(error);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        // Event listener untuk absen masuk
        document.getElementById('check_in_form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!latitude || !longitude) {
                Swal.fire({
                    title: 'Mendapatkan Lokasi...',
                    text: 'Mohon tunggu, sedang mengambil koordinat lokasi',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                getCurrentLocation()
                    .then(() => {
                        Swal.close();
                        submitCheckoutForm(this, null, false, 'check_in');
                    })
                    .catch(error => showLocationError(error));
            } else {
                submitCheckoutForm(this, null, false, 'check_in');
            }
        });

        // Event listener untuk absen keluar
        document.getElementById('check_out_form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!latitude || !longitude) {
                Swal.fire({
                    title: 'Mendapatkan Lokasi...',
                    text: 'Mohon tunggu, sedang mengambil koordinat lokasi',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                getCurrentLocation()
                    .then(() => {
                        Swal.close();
                        showCheckoutDialog();
                    })
                    .catch(error => showLocationError(error));
            } else {
                showCheckoutDialog();
            }
        });

        // Dialog input nomor WA
        function showCheckoutDialog() {
            Swal.fire({
                title: 'Absen Pulang',
                html: `
            <div class="mb-3 text-start">
                <label for="phone_number" class="form-label">Masukkan Nomor WhatsApp:</label>
                <input type="tel" class="form-control" id="phone_number" placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx" required>
                <small class="form-text text-muted">Format: 08xxxxxxxxxx atau 628xxxxxxxxxx</small>
            </div>
            <div class="form-check text-start">
                <input class="form-check-input" type="checkbox" id="auto_send_wa" checked>
                <label class="form-check-label" for="auto_send_wa">
                    Kirim otomatis ke WhatsApp setelah absen
                </label>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Kirim Absen',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                focusConfirm: false,
                preConfirm: () => {
                    const phoneNumber = document.getElementById('phone_number').value.trim();
                    const autoSendWA = document.getElementById('auto_send_wa').checked;

                    // Validasi yang lebih fleksibel untuk nomor Indonesia
                    const phoneRegex = /^(08|628|\+628|62|8)[0-9]{8,12}$/;

                    if (!phoneNumber) {
                        Swal.showValidationMessage('Nomor handphone harus diisi!');
                        return false;
                    }

                    if (!phoneRegex.test(phoneNumber.replace(/[^\d+]/g, ''))) {
                        Swal.showValidationMessage(
                            'Format nomor handphone tidak valid! Gunakan format: 08xxxxxxxxxx atau 628xxxxxxxxxx'
                        );
                        return false;
                    }

                    return {
                        phoneNumber,
                        autoSendWA
                    };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    const {
                        phoneNumber,
                        autoSendWA
                    } = result.value;
                    submitCheckoutForm(document.getElementById('check_out_form'), phoneNumber, autoSendWA,
                        'check_out');
                }
            });
        }

        // Submit form absen
        function submitCheckoutForm(form, phoneNumber = null, autoSendWA = true, type = 'check_out') {
            if (!latitude || !longitude || isNaN(latitude) || isNaN(longitude)) {
                return Swal.fire({
                    title: 'Error',
                    text: 'Koordinat lokasi tidak valid. Mohon refresh halaman dan coba lagi.',
                    icon: 'error'
                });
            }

            const formData = new FormData(form);
            formData.append('type', type);
            formData.append('latitude', latitude.toString());
            formData.append('longitude', longitude.toString());
            if (type === 'check_out' && phoneNumber) {
                formData.append('phone_number', phoneNumber);
            }

            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => handleResponse(data, type, autoSendWA))
                .catch(error => showRequestError(error));
        }

        // Tampilkan ringkasan & notifikasi hasil
        function handleResponse(data, type, autoSendWA) {
            Swal.close();

            if (data.status === 'success') {
                let icon = 'success';
                let title = type === 'check_in' ? 'Berhasil Absen Masuk!' : 'Berhasil Absen Pulang!';
                let html = data.message;

                if (data.attendance_info) {
                    const {
                        username,
                        check_in_time,
                        check_out_time,
                        phone_number
                    } = data.attendance_info;
                    html += `
                <div class="mt-3 p-3 border rounded bg-light text-start">
                    <h6 class="text-primary mb-2">📋 Ringkasan Absensi Hari Ini</h6>
                    <p class="mb-1"><strong>👤 Nama:</strong> ${username}</p>
                    <p class="mb-1"><strong>🕐 Jam Masuk:</strong> ${check_in_time}</p>
                    <p class="mb-1"><strong>🕔 Jam Pulang:</strong> ${check_out_time}</p>
                    <p class="mb-0"><strong>📱 Nomor HP:</strong> ${phone_number}</p>
                </div>`;
                }

                if (data.warning) {
                    icon = 'warning';
                    html += `<div class="alert alert-warning mt-2 mb-0">⚠️ ${data.warning}</div>`;
                }

                if (type === 'check_out' && autoSendWA && data.attendance_info) {
                    sendToWhatsApp(data.attendance_info);
                    html += `<div class="alert alert-success mt-2 mb-0">📱 Laporan telah dikirim ke WhatsApp!</div>`;
                }

                Swal.fire({
                    title,
                    html,
                    icon,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#198754'
                }).then(() => window.location.reload());

            } else {
                Swal.fire({
                    title: 'Gagal',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            }
        }

        // Error handling
        function showLocationError(error) {
            Swal.fire({
                title: 'Error',
                text: 'Gagal mendapatkan lokasi: ' + error.message,
                icon: 'error'
            });
        }

        function showRequestError(error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Terjadi kesalahan sistem: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }

        // Kirim laporan ke WhatsApp
        function sendToWhatsApp(attendanceInfo, type = 'check_out') {
            const {
                username,
                check_in_time,
                check_out_time,
                phone_number
            } = attendanceInfo;

            // Normalisasi nomor HP untuk format WhatsApp Indonesia
            let normalizedPhone = phone_number.toString().trim();

            // Hapus semua karakter non-digit kecuali +
            normalizedPhone = normalizedPhone.replace(/[^\d+]/g, '');

            // Konversi ke format internasional Indonesia
            if (normalizedPhone.startsWith('08')) {
                // 08xxxxxxxxx → 628xxxxxxxxx
                normalizedPhone = '62' + normalizedPhone.substring(1);
            } else if (normalizedPhone.startsWith('8')) {
                // 8xxxxxxxxx → 628xxxxxxxxx  
                normalizedPhone = '62' + normalizedPhone;
            } else if (normalizedPhone.startsWith('+62')) {
                // +628xxxxxxxxx → 628xxxxxxxxx
                normalizedPhone = normalizedPhone.substring(1);
            } else if (normalizedPhone.startsWith('62')) {
                // 628xxxxxxxxx → tetap 628xxxxxxxxx
                normalizedPhone = normalizedPhone;
            } else {
                // Format lain, anggap Indonesia dan tambahkan 62
                normalizedPhone = '62' + normalizedPhone;
            }

            console.log('Original phone:', phone_number);
            console.log('Normalized phone:', normalizedPhone);

            let message = '';

            if (type === 'check_in') {
                message = `
            *Laporan Absen Masuk*

            *Nama:* ${username}
            *Jam Masuk:* ${check_in_time}

            Selamat sekolah! Jangan lupa absen pulang nanti.
                    `.trim();
            } else {
                message = `
            *Laporan Absensi Hari Ini*

*Nama:* ${username}
*Jam Masuk:* ${check_in_time || 'Belum tercatat'}
*Jam Pulang:* ${check_out_time || 'Belum tercatat'}

Terima kasih telah melakukan absen hari ini.
                    `.trim();
            }

            const encodedMessage = encodeURIComponent(message);
            const whatsappURL = `https://wa.me/${normalizedPhone}?text=${encodedMessage}`;

            console.log('WhatsApp URL:', whatsappURL);
            window.open(whatsappURL, '_blank');
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
