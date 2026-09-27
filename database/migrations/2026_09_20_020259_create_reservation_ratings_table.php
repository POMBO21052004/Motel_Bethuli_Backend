<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Système de notation des réservations par le client.
     * Un client peut noter une réservation terminée (1 à 5 étoiles + commentaire).
     */
    public function up(): void
    {
        Schema::create('reservation_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relation avec la réservation évaluée (1 note par réservation max)
            $table->foreignUuid('reservation_id')->unique()->constrained('reservations')->onDelete('cascade');

            // Relation avec le client qui évalue
            $table->foreignUuid('client_id')->constrained('users')->onDelete('cascade');

            // Note globale de 1 à 5
            $table->unsignedTinyInteger('rating')->comment('Note de 1 (très mauvais) à 5 (excellent)');

            // Commentaire optionnel
            $table->text('comment')->nullable();

            // Aspects notés séparément
            $table->unsignedTinyInteger('cleanliness_rating')->nullable()->comment('Propreté de la chambre');
            $table->unsignedTinyInteger('service_rating')->nullable()->comment('Qualité du service');
            $table->unsignedTinyInteger('comfort_rating')->nullable()->comment('Confort');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_ratings');
    }
};
