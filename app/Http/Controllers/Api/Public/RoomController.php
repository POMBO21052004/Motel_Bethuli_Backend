<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Enums\RoomStatus;
use App\Enums\ReservationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with(['primaryImage', 'images'])
                     ->where('status', RoomStatus::AVAILABLE)
                     ->withAvg('ratings', 'rating')
                     ->orderByDesc('ratings_avg_rating')
                     ->orderBy('floor')
                     ->orderBy('name');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            
            $query->with(['reservations' => function($q) use ($startDate, $endDate) {
                $q->whereNotIn('status', [ReservationStatus::CANCELLED->value])
                  ->where('reservation_date', '<', $endDate)
                  ->where('end_date', '>', $startDate);
            }]);
        }

        if ($request->filled('limit')) {
            $rooms = $query->take($request->limit)->get();
        } else {
            $perPage = min((int) $request->get('per_page', 12), 50);
            $rooms = $query->paginate($perPage);
        }

        $items = $request->filled('limit') ? $rooms : $rooms->getCollection();

        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->format('Y-m-d');

        $items->transform(function ($room) use ($currentDate, $currentTime, $request) {
            // is_occupied_now logic
            $isOccupied = false;
            // Since we might have overridden reservations with the with() clause, we shouldn't rely on it for is_occupied_now if start_date is passed.
            // But actually we need both. Let's do a direct DB query for is_occupied_now to avoid relation conflict, or just let it be.
            $isOccupied = $room->reservations()
                ->where('status', ReservationStatus::CONFIRMED->value)
                ->whereDate('reservation_date', $currentDate)
                ->whereTime('start_time', '<=', $currentTime)
                ->whereTime('end_time', '>=', $currentTime)
                ->exists();
            $room->is_occupied_now = $isOccupied;

            // Date overlap logic
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $room->is_occupied_for_dates = $room->reservations->isNotEmpty();
                unset($room->reservations);
            } else {
                $room->is_occupied_for_dates = false;
            }

            return $room;
        });

        if ($request->filled('limit')) {
            return response()->json(['data' => $rooms]);
        }

        $rooms->setCollection($items);
        return response()->json([
            'pagination' => $rooms,
        ]);
    }

    public function show(string $id)
    {
        $room = Room::with([
            'images',
            'reservations.rating.client:id,nom,prenom',
        ])->findOrFail($id);
        
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->format('Y-m-d');

        $isOccupied = $room->reservations()
                ->where('status', ReservationStatus::CONFIRMED->value)
                ->whereDate('reservation_date', $currentDate)
                ->whereTime('start_time', '<=', $currentTime)
                ->whereTime('end_time', '>=', $currentTime)
                ->exists();

        $room->is_occupied_now = $isOccupied;

        // Collect all ratings from reservations
        $ratings = $room->reservations
            ->pluck('rating')
            ->filter()
            ->map(function ($rating) {
                return [
                    'id'                 => $rating->id,
                    'rating'             => $rating->rating,
                    'comment'            => $rating->comment,
                    'cleanliness_rating' => $rating->cleanliness_rating,
                    'service_rating'     => $rating->service_rating,
                    'comfort_rating'     => $rating->comfort_rating,
                    'created_at'         => $rating->created_at,
                    'client'             => $rating->client ? [
                        'prenom' => $rating->client->prenom,
                        'nom'    => mb_substr($rating->client->nom, 0, 1) . '.',
                    ] : null,
                ];
            })
            ->values();

        $avgRating = $ratings->avg('rating');

        $room->ratings = $ratings;
        $room->avg_rating = $avgRating ? round($avgRating, 1) : null;
        $room->ratings_count = $ratings->count();

        // Remove reservations from output (only expose ratings)
        unset($room->reservations);

        return response()->json(['data' => $room]);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'reservation_date' => 'required|date',
            'end_date'         => 'required|date|after:reservation_date',  // minimum 1 nuit
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if ($room->status->value !== 'available') {
            return response()->json([
                'available'   => false,
                'message'     => 'Cette chambre est actuellement en maintenance ou indisponible.',
                'occupied_by' => null,
                'from'        => null,
                'until'       => null,
            ]);
        }

        $excludedStatuses = [
            \App\Enums\ReservationStatus::CANCELLED->value,
        ];

        $conflict = \App\Models\Reservation::where('room_id', $room->id)
            ->whereNotIn('status', $excludedStatuses)
            ->where(function ($q) use ($validated) {
                $q->whereDate('reservation_date', '<', $validated['end_date'])
                  ->whereDate('end_date', '>', $validated['reservation_date']);
            })
            ->first();

        if ($conflict) {
            $from  = \Carbon\Carbon::parse($conflict->reservation_date)->format('d/m/Y');
            $until = \Carbon\Carbon::parse($conflict->end_date)->format('d/m/Y');
            return response()->json([
                'available' => false,
                'message'   => "La chambre est déjà réservée du {$from} au {$until}.",
                'from'      => $conflict->reservation_date,
                'until'     => $conflict->end_date,
            ]);
        }

        $days = Carbon::parse($validated['reservation_date'])->diffInDays(Carbon::parse($validated['end_date']));
        $days = $days == 0 ? 1 : $days;

        return response()->json([
            'available' => true,
            'message'   => 'La chambre est disponible pour ces dates.',
            'price'     => ($room->price_per_day ?? 0) * $days,
            'days'      => $days,
        ]);
    }
}
