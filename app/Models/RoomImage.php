<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'room_id',
        'image_path',
        'is_primary',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
