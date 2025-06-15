@extends('layouts.user')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="/index">
                    <img src="{{ asset('bahan/back (2).png') }}" style="width: 50px">
                </a>
                <h4 class="text-light my-3">Absensi</h4>
            </div>
        </div>

        {{-- Absen lokasi --}}
        <div class="container mt-3">
            <h4 class="text-light my-3">Lokasi</h4>
            <table class="table table-responsive table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Jarak Absen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->created_at->format('j F Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->check_in_time)->format('H:i') }}</td>
                            <td> {{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : '-' }}
                            </td>
                            <td>{{ $item->distance }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Absen camera --}}
        <div class="container mt-3">
            <h4 class="text-light my-3">Kamera</h4>
            <table class="table table-responsive table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cameraAttendances as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($item->photo_path)
                                    @php
                                        $photoName = $item->photo_path;
                                        if (strpos($photoName, 'attendance_photos/') === 0) {
                                            $photoName = substr($photoName, strlen('attendance_photos/'));
                                        }
                                    @endphp
                                    <img src="{{ asset('storage/' . $item->photo_path) }}" alt="Foto Absen">
                                @else
                                    <span class="text-yellow-500">Tidak ada foto</span>
                                @endif
                            </td>
                            <td>{{ $item->check_in ?? '-' }}</td>
                            <td>{{ $item->check_out ?? '-' }}</td>
                            <td>{{ $item->latitude }}</td>
                            <td>{{ $item->longitude }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
