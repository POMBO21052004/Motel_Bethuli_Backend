<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'floor',
        'description_fr',
        'description_en',
        'capacity',
        'price_per_hour',
        'price_per_day',
        'status',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'status'   => RoomStatus::class,
            'capacity' => 'integer',
            'floor'    => 'integer',
            'features' => 'array',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', RoomStatus::AVAILABLE);
    }

    public function scopeByFloor($query, int $floor)
    {
        return $query->where('floor', $floor);
    }

    // ─── Accesseur ───────────────────────────────────────────────────────────

    /**
     * Retourne le libellé de l'étage (ex : "Rez-de-chaussée", "1er étage"…).
     */
    public function getFloorLabelAttribute(): string
    {
        return $this->floor === 0
            ? 'Rez-de-chaussée'
            : $this->floor . (($this->floor === 1) ? 'er' : 'ème') . ' étage';
    }

    protected $appends = ['floor_label'];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RoomImage::class);
    }

    public function primaryImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RoomImage::class)->where('is_primary', true);
    }

    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
