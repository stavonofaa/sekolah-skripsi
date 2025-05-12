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
        // // Hanya aktifkan satu lokasi
        Location::create([
            'name_location' => 'SMA Negeri 1 Cikarang Timur',
            'address' => 'Jl. Raya Citarik No.2, RT.11/RW.6, Jatibaru, Kec. Cikarang Tim., Kabupaten Bekasi, Jawa Barat 17530',
            'latitude' => '-6.274356189745559',
            'longitude' => '107.20150362262028',
            'radius' => 10.0,
            'is_active' => 1,
        ]);

        // // Hanya aktifkan satu lokasi
        // Location::create([
        //     'name_location' => 'Jalan Kavling Bulak Macan E',
        //     'address' => 'RT.004/RW.022, Harapan Jaya, Kec. Bekasi Utara, Kota Bks, Jawa Barat 17124',
        //     'latitude' => '-6.209852876146287',
        //     'longitude' => '106.99214995145515',
        //     'radius' => 10.0,
        //     'is_active' => 1,
        // ]);
    }
}
