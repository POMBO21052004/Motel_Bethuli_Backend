<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationRating;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user()->load('customerProfile');
        $now    = Carbon::now();
        $today  = $now->toDateString();
        $time   = $now->format('H:i:s');

        // ── Stats personnelles ────────────────────────────────────────────────
        $stats = [
            'total'         => $client->reservations()->count(),
            'pending'       => $client->reservations()->where('status', ReservationStatus::PENDING)->count(),
            'confirmed'     => $client->reservations()->where('status', ReservationStatus::CONFIRMED)->count(),
            'ratings_count' => ReservationRating::where('client_id', $client->id)->count(),
        ];

        // ── Prochaine réservation (la plus proche dans le futur ou en cours) ──
        $nextReservation = Reservation::where('client_id', $client->id)
            ->whereIn('status', [ReservationStatus::PENDING->value, ReservationStatus::CONFIRMED->value])
            ->where(function ($q) use ($today) {
                $q->whereDate('reservation_date', '>=', $today);
            })
            ->with(['room:id,name,description_fr,floor,price_per_day', 'room.primaryImage:id,room_id,image_path'])
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->first();

        // ── 3 dernières réservations ──────────────────────────────────────────
        $recentReservations = Reservation::where('client_id', $client->id)
            ->with(['room:id,name,description_fr', 'room.primaryImage:id,room_id,image_path'])
            ->latest('reservation_date')
            ->limit(3)
            ->get();

        // ── Chambres disponibles (6 max) avec statut d'occupation en temps réel
        $rooms = Room::where('status', RoomStatus::AVAILABLE)
            ->with(['primaryImage:id,room_id,image_path'])
            ->orderBy('floor')
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(function ($room) use ($today, $time) {
                $room->is_occupied_now = $room->reservations()
                    ->where('status', ReservationStatus::CONFIRMED->value)
                    ->whereDate('reservation_date', $today)
                    ->whereTime('start_time', '<=', $time)
                    ->whereTime('end_time', '>=', $time)
                    ->exists();
                return $room;
            });

        $availableRoomsCount = Room::where('status', RoomStatus::AVAILABLE)->count();

        return response()->json([
            'user'                  => $client,
            'stats'                 => $stats,
            'next_reservation'      => $nextReservation,
            'recent_reservations'   => $recentReservations,
            'available_rooms'       => $rooms,
            'available_rooms_count' => $availableRoomsCount,
        ]);
    }
}
