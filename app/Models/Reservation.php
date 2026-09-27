<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'room_id',
        'client_id',
        'receptionist_id',
        'reservation_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'           => ReservationStatus::class,
            'reservation_date' => 'date',
            'total_price'      => 'decimal:2',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', ReservationStatus::PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', ReservationStatus::CONFIRMED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', ReservationStatus::COMPLETED);
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Client (utilisateur) ayant effectué la réservation.
     */
    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Réceptionniste ayant saisi la réservation sur place (nullable).
     */
    public function receptionist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    /**
     * Notation laissée par le client sur cette réservation.
     */
    public function rating(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReservationRating::class);
    }
}
