<?php

namespace App\Enums;

enum UserRole: string
{
    case CLIENT      = 'client';
    case RECEPTIONIST = 'receptionniste';
    case ADMIN       = 'admin';

    /**
     * Retourne le libellé humain du rôle.
     */
    public function label(): string
    {
        return match($this) {
            self::CLIENT       => 'Client',
            self::RECEPTIONIST => 'Réceptionniste',
            self::ADMIN        => 'Administrateur',
        };
    }

    /**
     * Retourne toutes les valeurs utilisables dans les migrations/validations.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
