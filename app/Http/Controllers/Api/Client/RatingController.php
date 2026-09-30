<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ReservationRating;
use App\Models\Reservation;
use App\Enums\ReservationStatus;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => ReservationRating::where('client_id', $request->user()->id)
                ->with('reservation.room:id,name')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        
        $reservation = Reservation::where('id', $validated['reservation_id'])
            ->where('client_id', $user->id)
            ->firstOrFail();

        if ($reservation->status !== ReservationStatus::CONFIRMED && $reservation->status !== ReservationStatus::COMPLETED) {
            return response()->json(['message' => 'Vous ne pouvez noter qu\'une réservation confirmée ou terminée.'], 403);
        }

        if (ReservationRating::where('reservation_id', $reservation->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà noté cette réservation.'], 403);
        }

        $rating = ReservationRating::create([
            'reservation_id' => $reservation->id,
            'client_id' => $user->id,
            'room_id' => $reservation->room_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? '',
        ]);

        return response()->json(['message' => 'Merci pour votre avis !', 'data' => $rating], 201);
    }
}