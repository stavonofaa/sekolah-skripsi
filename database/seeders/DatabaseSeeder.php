<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::create([
        //     'name' => 'admin',
        //     'username' => 'admin123',
        //     'password' => bcrypt(123),
        //     'role' => 'admin',
        // ]);
        // User::create([
        //     'name' => 'karyawan1',
        //     'username' => 'karyawan123',
        //     'password' => bcrypt(123),
        //     'password_show' => 123,
        //     'role' => 'user',
        //     'jabatan' => 'karyawan',
        // ]);
        // User::create([
        //     'name' => 'karyawan2',
        //     'username' => 'karyawan1234',
        //     'password' => bcrypt(123),
        //     'password_show' => 123,
        //     'role' => 'user',
        //     'jabatan' => 'karyawan',
        // ]);
        // User::create([
        //     'name' => 'karyawan3',
        //     'username' => 'karyawan1235',
        //     'password' => bcrypt(123),
        //     'password_show' => 123,
        //     'role' => 'user',
        //     'jabatan' => 'karyawan',
        // ]);

        // Hanya aktifkan satu lokasi
        Location::create([
            'name_location' => 'barokah tour & travel',
            'address' => 'jalan raya cisaat',
            'latitude' => '-6.909357607107361',
            'longitude' => '106.89405757822857',
            'radius' => 100.0,
            'is_active' => 1,
        ]);

        Location::create([
            'name_location' => 'Harapan Jaya',
            'address' => 'Jl. Macan E, Harapan Jaya, Bekasi Utara, Kota Bekasi, Jawa Barat 17124',
            'latitude' => '-6.2207564',
            'longitude' => '106.9870886',
            'radius' => 1000.0,
            'is_active' => 0,
        ]);
    }
}
