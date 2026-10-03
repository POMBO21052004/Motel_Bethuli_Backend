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

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'reservation_id'     => 'required|exists:reservations,id',
            'comment'            => 'nullable|string|max:1000',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'service_rating'     => 'required|integer|min:1|max:5',
            'comfort_rating'     => 'required|integer|min:1|max:5',
        ]);

        $user = $request->user();

        $reservation = Reservation::where('id', $validated['reservation_id'])
            ->where('client_id', $user->id)
            ->firstOrFail();

        if ($reservation->status !== ReservationStatus::CONFIRMED) {
            return response()->json(['message' => 'Vous ne pouvez noter qu\'une réservation confirmée.'], 403);
        }

        $computedRating = (int) round(($validated['cleanliness_rating'] + $validated['service_rating'] + $validated['comfort_rating']) / 3);

        $rating = ReservationRating::updateOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'client_id'          => $user->id,
                'rating'             => $computedRating,
                'comment'            => $validated['comment'] ?? '',
                'cleanliness_rating' => $validated['cleanliness_rating'],
                'service_rating'     => $validated['service_rating'],
                'comfort_rating'     => $validated['comfort_rating'],
            ]
        );

        return response()->json(['message' => 'Votre avis a bien été enregistré.', 'data' => $rating], 200);
    }
}