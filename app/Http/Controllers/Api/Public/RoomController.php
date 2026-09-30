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
                     ->orderBy('floor')
                     ->orderBy('name');

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
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

        $items->transform(function ($room) use ($currentDate, $currentTime) {
            // Check if there is an active confirmed reservation right now
            $isOccupied = $room->reservations()
                ->where('status', ReservationStatus::CONFIRMED->value)
                ->whereDate('reservation_date', $currentDate)
                ->whereTime('start_time', '<=', $currentTime)
                ->whereTime('end_time', '>=', $currentTime)
                ->exists();

            $room->is_occupied_now = $isOccupied;
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
        $room = Room::with(['images'])->findOrFail($id);
        
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

        return response()->json(['data' => $room]);
    }
}
