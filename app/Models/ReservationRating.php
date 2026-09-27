<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationRating extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'reservation_id',
        'client_id',
        'rating',
        'comment',
        'cleanliness_rating',
        'service_rating',
        'comfort_rating',
    ];

    protected function casts(): array
    {
        return [
            'rating'             => 'integer',
            'cleanliness_rating' => 'integer',
            'service_rating'     => 'integer',
            'comfort_rating'     => 'integer',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    /**
     * Réservation évaluée.
     */
    public function reservation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Client auteur de la notation.
     */
    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // ─── Accesseur ───────────────────────────────────────────────────────────

    /**
     * Calcule la note globale moyenne à partir des sous-notes si renseignées.
     */
    public function getAverageRatingAttribute(): float
    {
        $subRatings = array_filter([
            $this->cleanliness_rating,
            $this->service_rating,
            $this->comfort_rating,
        ]);

        if (empty($subRatings)) {
            return (float) $this->rating;
        }

        return round(array_sum($subRatings) / count($subRatings), 1);
    }
}
