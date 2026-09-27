<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('reservation_date');
        });

        // Mettre à jour les anciennes données pour que la date de fin soit identique à la date de début (séjours d'une journée)
        DB::statement('UPDATE reservations SET end_date = reservation_date');

        // Maintenant on peut la rendre non-nulle si on le souhaite
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('end_date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};
