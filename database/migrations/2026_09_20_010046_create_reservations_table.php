<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refonte : UUID partout, user_id pour lier la réservation à un User.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_id')->constrained('rooms')->onDelete('cascade');

            // Lien vers le client qui réserve
            $table->foreignUuid('client_id')->constrained('users')->onDelete('cascade');

            // Lien optionnel vers la réceptionniste qui a saisi la réservation sur place
            $table->foreignUuid('receptionist_id')->nullable()->constrained('users')->onDelete('set null');

            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable()->comment('Remarques éventuelles de la réceptionniste');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
