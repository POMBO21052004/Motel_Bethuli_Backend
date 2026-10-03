<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::CANCELLED => 'Annulée',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
