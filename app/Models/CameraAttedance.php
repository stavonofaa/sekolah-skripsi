<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CameraAttedance extends Model
{
    use HasFactory;

    protected $table = 'camera_attendances';

    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'photo_in',
        'photo_out',
        'latitude',
        'longitude',
        'latitude_out',
        'longitude_out',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
