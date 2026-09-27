<?php

namespace App\Enums;

enum RoomStatus: string
{
    case AVAILABLE   = 'available';
    case MAINTENANCE = 'maintenance';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE   => 'Disponible',
            self::MAINTENANCE => 'En maintenance',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
