<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    /**
     * Toutes les chambres groupées par étage, avec statut d'occupation en temps réel.
     */
    public function index(Request $request)
    {
        $now   = Carbon::now();
        $today = $now->toDateString();
        $time  = $now->format('H:i:s');

        $rooms = Room::with(['primaryImage:id,room_id,image_path', 'images:id,room_id,image_path,is_primary'])
            ->orderBy('floor')
            ->orderBy('name')
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

        // Grouper par étage
        $grouped = $rooms->groupBy('floor')->map(function ($rooms, $floor) {
            $label = $floor === 0 ? 'Rez-de-chaussée' : $floor . ($floor === 1 ? 'er' : 'ème') . ' Étage';
            return [
                'floor'       => (int) $floor,
                'floor_label' => $label,
                'rooms'       => $rooms->values(),
            ];
        })->sortKeys()->values();

        return response()->json(['data' => $grouped]);
    }
}
