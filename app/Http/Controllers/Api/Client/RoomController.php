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
     * Toutes les chambres, filtrables.
     */
    public function index(Request $request)
    {
        $now   = Carbon::now();
        $today = $now->toDateString();
        $time  = $now->format('H:i:s');

        $query = Room::with(['primaryImage', 'images'])->orderBy('floor')->orderBy('name');

        // Available between start_date and end_date
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            
            $query->with(['reservations' => function($q) use ($startDate, $endDate) {
                $q->whereNotIn('status', [ReservationStatus::CANCELLED->value])
                  ->where('reservation_date', '<', $endDate)
                  ->where('end_date', '>', $startDate);
            }]);
        }

        $perPage = min((int) $request->get('per_page', 50), 100);
        $rooms = $query->paginate($perPage);

        // Append is_occupied_now logic
        $rooms->getCollection()->transform(function ($room) use ($today, $time, $request) {
            $room->is_occupied_now = $room->reservations()
                ->where('status', ReservationStatus::CONFIRMED->value)
                ->whereDate('reservation_date', $today)
                ->whereTime('start_time', '<=', $time)
                ->whereTime('end_time', '>=', $time)
                ->exists();

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $room->is_occupied_for_dates = $room->reservations->isNotEmpty();
                unset($room->reservations);
            } else {
                $room->is_occupied_for_dates = false;
            }

            return $room;
        });

        return response()->json([
            'pagination' => $rooms
        ]);
    }
}
