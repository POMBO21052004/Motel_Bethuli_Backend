<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil détaillé du client (informations d'identité).
     * Séparé de la table users pour ne pas surcharger cette dernière.
     */
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relation 1:1 avec l'utilisateur (rôle client)
            $table->foreignUuid('user_id')->unique()->constrained('users')->onDelete('cascade');

            // Pièce d'identité (CNI = Carte Nationale d'Identité)
            $table->string('cni_number')->nullable()->unique()->comment("Numéro de la Carte Nationale d'Identité");
            $table->string('cni_recto_path')->nullable()->comment('Chemin vers la photo recto de la CNI');
            $table->string('cni_verso_path')->nullable()->comment('Chemin vers la photo verso de la CNI');
            $table->boolean('cni_verified')->default(false)->comment('CNI vérifiée par un admin/réceptionniste');

            // Informations complémentaires
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->default('Cameroun');
            $table->string('nationalite')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
