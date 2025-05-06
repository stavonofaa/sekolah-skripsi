<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Location;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // biarkan saja error gak ngaruh
        $attendances = Auth::user()->attendances()->latest()->limit(3)->get();
        return view('user-absensi.index', compact('attendances'));
    }

    public function riwayatAbsen()
    {
        $attendances = Attendance::with('location')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        // Menghitung jarak untuk setiap absensi
        foreach ($attendances as $attendance) {
            $attendance->distance = $attendance->calculateDistance();
        }

        // Mengirim data ke view
        return view('user-absensi.riwayatAbsen', compact('attendances'));
    }
    public function profile()
    {
        return view('user-absensi.profile');
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'type' => 'required|in:check_in,check_out'
        ]);

        $user_id = Auth::id();
        $today = now()->toDateString();
        $currentTime = now();

        // Dapatkan lokasi aktif
        $location = Location::where('is_active', true)->first();
        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada lokasi instansi yang aktif'
            ]);
        }

        // Hitung jarak dengan lokasi
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $location->latitude,
            $location->longitude
        );

        // Validasi radius maksimum
        $maxRadius = $location->radius ?? 100; //default 100 m
        if ($distance > $maxRadius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda berada diluar radius sekolah (' . $distance . ' meter)'
            ]);
        }

        // Cek absensi hari ini
        $attendance = Attendance::where('user_id', $user_id)
            ->whereDate('created_at', $today)
            ->first();

        // Jam kerja
        $startTime = Carbon::parse('07.00');
        $endTime = Carbon::parse('16.00');

        // Absen masuk
        if ($request->type == 'check_in') {
            if ($attendance && $attendance->check_in_time) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah absen masuk hari ini'
                ]);
            }

            // Cek keterlambatan
            $isLate = $currentTime->greaterThan($startTime);
            $lateMinutes = $isLate ? $currentTime->diffInMinutes($startTime) : 0;

            // Simpan absen
            if (!$attendance) {
                $attendance = new Attendance();
                $attendance->user_id = $user_id;
                $attendance->location_id = $location->id;
            }

            $attendance->check_in_time = $currentTime;
            $attendance->check_in_lat = $request->latitude;
            $attendance->check_in_long = $request->longitude;
            $attendance->is_late = $isLate;
            $attendance->late_minutes = $lateMinutes;
            $attendance->save();

            // response
            if ($isLate) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Absen Masuk Berhasil',
                    'warning' => 'Anda terlambat ' . $lateMinutes . ' menit'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absen Masuk Berhasil'
            ]);
        }

        // Absen pulang
        else {
            if (!$attendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum masuk hari ini'
                ]);
            }

            if ($attendance->check_out_time) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah absen pulang hari ini'
                ]);
            }

            // Cek pulang cepat
            $isEarly = $currentTime->lessThan($endTime);
            $earlyMinutes = $isEarly ? $currentTime->diffInMinutes($endTime) : 0;

            // Simpan absen pulang
            $attendance->check_out_time = $currentTime;
            $attendance->check_out_lat = $request->latitude;
            $attendance->check_out_long = $request->longitude;
            $attendance->is_early = $isEarly;
            $attendance->early_minutes = $earlyMinutes;
            $attendance->save();

            // Response
            if ($isEarly) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Absen Pulang Berhasil',
                    'warning' => 'Anda pulang lebih awal ' . $earlyMinutes . ' menit'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absen Pulang Berhasil'
            ]);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance);
    }
}
