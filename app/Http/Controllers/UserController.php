<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Location;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\CameraAttedance;
use App\Models\User;
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

        $cameraAttendances = CameraAttedance::select('id', 'photo_in', 'photo_out', 'latitude', 'check_in', 'check_out', 'longitude', 'latitude_out', 'longitude_out', 'created_at')
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
            $type = $request->input('type');

            if (!in_array($type, ['check_in', 'check_out'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe absen tidak valid'
                ], 400);
            }

            $rules = [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'type' => 'required|in:check_in,check_out',
            ];

            if ($type === 'check_out') {
                $rules['phone_number'] = 'required|string';
            }

            try {
                $request->validate($rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errors = collect($e->errors())->flatten()->toArray();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak valid: ' . implode(', ', $errors)
                ], 422);
            }

            if ($type === 'check_out') {
                $phoneNumber = trim($request->phone_number);

                if (
                    !str_starts_with($phoneNumber, '08') &&
                    !str_starts_with($phoneNumber, '628') &&
                    !str_starts_with($phoneNumber, '+628')
                ) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Nomor handphone harus dimulai dengan 08, 628, atau +628'
                    ], 400);
                }

                $cleanPhone = str_replace('+', '', $phoneNumber);
                if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 13) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Panjang nomor handphone tidak valid'
                    ], 400);
                }
            }

            $user_id = Auth::id();
            if (!$user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            $now = now();
            $today = $now->toDateString();

            try {
                $location = Location::first();
                if (!$location) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tidak ada lokasi instansi yang aktif'
                    ], 400);
                }

                $attendance = Attendance::where('user_id', $user_id)
                    ->whereDate('created_at', $today)
                    ->first();
            } catch (\Exception $e) {
                Log::error('Database query error: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kesalahan mengakses database'
                ], 500);
            }

            $startTime = Carbon::parse('07:00')->setDateFrom($now);
            $endTime   = Carbon::parse('16:00')->setDateFrom($now);

            if ($type === 'check_in') {
                if ($attendance && $attendance->check_in_time) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda sudah absen masuk hari ini'
                    ], 400);
                }

                $isLate = $now->greaterThan($startTime);
                $lateMinutes = $isLate ? $now->diffInMinutes($startTime) : 0;

                try {
                    if (!$attendance) {
                        $attendance = new Attendance([
                            'user_id' => $user_id,
                            'location_id' => $location->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $attendance->fill([
                        'check_in_time' => $now,
                        'check_in_lat' => $request->latitude,
                        'check_in_long' => $request->longitude,
                        'is_late' => $isLate,
                        'late_minutes' => $lateMinutes,
                        'updated_at' => $now,
                    ])->save();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Absen Masuk Berhasil' . ($isLate ? ', Anda terlambat ' . $lateMinutes . ' menit' : '')
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error saving check-in: ' . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data absen masuk'
                    ], 500);
                }
            }

            if ($type === 'check_out') {
                if (!$attendance || !$attendance->check_in_time) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda belum absen masuk hari ini'
                    ], 400);
                }

                if ($attendance->check_out_time) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda sudah absen pulang hari ini'
                    ], 400);
                }

                $isEarly = $now->lessThan($endTime);
                $earlyMinutes = $isEarly ? $endTime->diffInMinutes($now) : 0;

                try {
                    $attendance->fill([
                        'check_out_time' => $now,
                        'check_out_lat' => $request->latitude,
                        'check_out_long' => $request->longitude,
                        'is_early' => $isEarly,
                        'early_minutes' => $earlyMinutes,
                        'phone_number' => $request->phone_number,
                        'updated_at' => $now,
                    ])->save();

                    $attendance->refresh();

                    $user = Auth::user();
                    if (!$user) {
                        throw new \Exception('User data not found');
                    }

                    $info = [
                        'username' => $user->name,
                        'check_in_time' => $attendance->check_in_time
                            ? $attendance->check_in_time->format('H:i')
                            : 'Belum Absen Masuk',
                        'check_out_time' => $now->format('H:i'),
                        'phone_number' => $request->phone_number,
                    ];

                    $response = [
                        'status' => 'success',
                        'message' => 'Absen Pulang Berhasil',
                        'attendance_info' => $info,
                    ];

                    if ($isEarly) {
                        $response['warning'] = 'Anda pulang lebih awal ' . $earlyMinutes . ' menit';
                    }

                    return response()->json($response);
                } catch (\Exception $e) {
                    Log::error('Error saving check-out: ' . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data absen pulang'
                    ], 500);
                }
            }

            // Default fallback
            return response()->json([
                'status' => 'error',
                'message' => 'Tipe absen tidak dikenali'
            ], 400);
        } catch (\Exception $e) {
            Log::error('General error in store method: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    public function cameraAbsensi(Request $request)
    {
        // Validasi input
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $photo = $request->file('photo');
        $photoPath = $photo->store('attendance_photos', 'public');

        $userId = Auth::id();
        // validasi apakah user sudah login
        if (!$userId) {
            return redirect()->back()->with('error', 'User tidak terautentikasi');
        }
        $today = now()->format('Y-m-d');

        // cek apakah user sudah absen hari ini
        $existingAttendance = CameraAttedance::where('user_id', $userId)
            ->whereDate('check_in', $today)
            ->first();

        // Pengecekan ketika sudah absen hari ini masuk dan keluar
        if ($existingAttendance) {
            if ($existingAttendance->check_out === null) {
                // Absen keluar
                $existingAttendance->update([
                    'check_out' => now(),
                    'latitude_out' => $request->latitude,
                    'longitude_out' => $request->longitude,
                    'photo_out' => $photoPath,
                ]);

                return redirect()->back()->with('success', 'Absensi keluar berhasil direkam');
            } else {
                // Sudah absen masuk & keluar
                return redirect()->back()->with('warning', 'Anda sudah absen masuk dan pulang hari ini');
            }
        }
        // Absen masuk
        CameraAttedance::create([
            'user_id' => $userId,
            'photo_in' => $photoPath,
            'check_in' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->back()->with('success', 'Absensi masuk berhasil direkam');
    }
}
