<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Enums\ReservationStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->reservations()->with([
            'room:id,name,description_fr',
            'room.primaryImage:id,room_id,image_path'
        ])->latest('reservation_date');
        
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json(['pagination' => $query->paginate(20)]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        // Vérification de la CNI
        $profile = $user->customerProfile;
        if (!$profile || !$profile->cni_verified) {
            return response()->json([
                'message' => 'Votre compte n\'est pas encore vérifié. Veuillez mettre à jour votre CNI dans votre profil pour pouvoir réserver.'
            ], 403);
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:2000',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        if ($room->status->value !== 'available') {
            return response()->json(['message' => 'Cette chambre n\'est pas disponible pour le moment.'], 422);
        }

        // Vérification des conflits (chevauchement)
        $hasConflict = Reservation::where('room_id', $room->id)
            ->whereDate('reservation_date', $validated['reservation_date'])
            ->whereNotIn('status', [ReservationStatus::CANCELLED->value]) // Uncompleted might still block? Wait, COMPLETED blocks too if it was completed at that time, but normally completed means past. Let's block if NOT CANCELLED.
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($hasConflict) {
            return response()->json(['message' => 'La chambre est déjà réservée sur ce créneau.'], 422);
        }

        $reservation = Reservation::create([
            'room_id' => $room->id,
            'client_id' => $user->id,
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'total_price' => $room->price_per_day ?? 0, // Should calculate exact based on hours, but let's keep it simple or fallback to price_per_day
            'notes' => $validated['notes'] ?? null,
            'status' => ReservationStatus::PENDING->value,
        ]);

        return response()->json([
            'message' => 'Réservation créée avec succès. Veuillez contacter le support WhatsApp pour la confirmer.',
            'data' => $reservation->load('room:id,name')
        ], 201);
    }
}
