<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Supprime le statut "occupied" des chambres (qui est désormais déduit des réservations actives)
     * et supprime le statut "completed" des réservations.
     *
     * ATTENTION : Met à jour toutes les lignes ayant l'ancien statut
     * avant de modifier l'enum pour éviter une erreur de contrainte SQL.
     */
    public function up(): void
    {
        // 1. Migrer les chambres occupées → disponible
        DB::table('rooms')
            ->where('status', 'occupied')
            ->update(['status' => 'available']);

        // 2. Migrer les réservations "completed" → confirmed
        //    (une réservation passée confirmée reste confirmée — le statut est historique)
        DB::table('reservations')
            ->where('status', 'completed')
            ->update(['status' => 'confirmed']);

        // 3. Modifier l'enum rooms.status (syntaxe MySQL/MariaDB)
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'maintenance') NOT NULL DEFAULT 'available'");

        // 4. Modifier l'enum reservations.status
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Restaurer les anciens enums si nécessaire (rollback)
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available'");
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
