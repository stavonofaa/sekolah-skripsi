<div class="card bg-light rounded-3 mb-3">
    <div class="card-body">
        <video class="w-100 h-100" id="video" autoplay></video>
        <canvas class="d-none w-100 h-100" id="canvas"></canvas>
        <form id="attendanceForm" action="{{ route('user.cameraAbsensi') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="file" name="photo" id="photoFile" class="d-none">
            <button type="button" id="capture" class="btn btn-primary mt-3">Ambil Foto</button>
            <button type="submit" id="submitForm" class="btn btn-success mt-3 d-none">Kirim Absensi</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureButton = document.getElementById('capture');
    const submitButton = document.getElementById('submitForm');
    const photoFile = document.getElementById('photoFile');
    const form = document.getElementById('attendanceForm');
    let stream;

    // Mulai kamera
    const startCamera = () => {
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(s => {
                stream = s;
                video.srcObject = s;
                getLocation();
            })
            .catch(err => alert(`Gagal akses kamera: ${err}`));
    };

    // Dapatkan lokasi
    const getLocation = () => {
        captureButton.disabled = true;

        if (!navigator.geolocation) {
            alert('Geolokasi tidak didukung');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('latitude').value = pos.coords.latitude;
                document.getElementById('longitude').value = pos.coords.longitude;
                captureButton.disabled = false;
            },
            err => alert(`Error lokasi: ${err.message}`)
        );
    };

    // Event saat tab kamera dibuka
    document.querySelector('#camera-tab').addEventListener('shown.bs.tab', startCamera);

    // Hentikan kamera saat tab ditutup
    document.querySelector('#camera-tab').addEventListener('hidden.bs.tab', () => {
        stream?.getTracks().forEach(track => track.stop());
    });

    // Ambil foto
    captureButton.addEventListener('click', () => {
        canvas.classList.remove('d-none');

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(blob => {
            const file = new File([blob], `absensi_${Date.now()}.png`, {
                type: 'image/png'
            });
            const dt = new DataTransfer();
            dt.items.add(file);
            photoFile.files = dt.files;

            submitButton.classList.remove('d-none');
            captureButton.textContent = 'Ambil Ulang';
        }, 'image/png');
    });

    // Validasi form
    form.addEventListener('submit', e => {
        if (!photoFile.files?.length) {
            e.preventDefault();
            alert('Ambil foto terlebih dahulu');
            return;
        }

        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        if (!lat || !lng) {
            e.preventDefault();
            Swal.fire({
                title: 'Informasi',
                text: 'Data lokasi belum tersedia',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            alert('Data lokasi belum tersedia');
            getLocation();
        }
    });
</script>

{{-- notifikasi dari backend  --}}
@if (session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if (session('warning'))
    <script>
        Swal.fire({
            title: 'Peringatan',
            text: '{{ session('warning') }}',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            title: 'Gagal',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif
