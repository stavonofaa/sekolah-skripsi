<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Location;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\CameraAttedance;
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

        $cameraAttendances = CameraAttedance::select('id', 'photo_path', 'latitude', 'longitude', 'created_at')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        // Menghitung jarak untuk setiap absensi
        foreach ($attendances as $attendance) {
            $attendance->distance = $attendance->calculateDistance();
        }

        // Mengirim data ke view
        return view('user-absensi.riwayatAbsen', compact('attendances', 'cameraAttendances'));
    }

    public function profile()
    {
        return view('user-absensi.profile');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'type' => 'required|in:check_in,check_out'
            ]);

            $user_id = Auth::id();
            $today = now()->toDateString();
            $currentTime = now();

            // Dapatkan lokasi aktif
            $location = Location::first();
            if (!$location) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada lokasi instansi yang aktif'
                ], 400);
            }

            // Cek absensi hari ini
            $attendance = Attendance::where('user_id', $user_id)
                ->whereDate('created_at', $today)
                ->first();

            // Jam sekolah
            $startTime = Carbon::parse('07:00');
            $endTime = Carbon::parse('16:00');

            // Absen masuk
            if ($request->type == 'check_in') {
                if ($attendance && $attendance->check_in_time) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda sudah absen masuk hari ini'
                    ], 400);
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

                // Response
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
                    ], 400);
                }

                if ($attendance->check_out_time) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda sudah absen pulang hari ini'
                    ], 400);
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
        } catch (\Exception $e) {
            Log::error('Error in store function: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cameraAbsensi(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $photo = $request->file('photo');
        $photoPath = $photo->store('attendance_photos', 'public');

        CameraAttedance::create([
            'user_id' => Auth::id(),
            'check_in' => now(),
            'photo_path' => $photoPath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return redirect()->back()->with('success', 'Absensi berhasil direkam');
    }
}
