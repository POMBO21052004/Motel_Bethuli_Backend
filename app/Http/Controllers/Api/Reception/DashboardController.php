<?php

namespace App\Http\Controllers\Api\Reception;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'available_rooms' => Room::where('status', 'available')->count(),
                'occupied_rooms' => Room::where('status', 'occupied')->count(),
                'pending_reservations' => Reservation::where('status', ReservationStatus::PENDING)->count(),
                'today_reservations' => Reservation::whereDate('reservation_date', today())->count(),
                'clients' => User::where('role', 'client')->count(),
            ],
            'recent_reservations' => Reservation::with(['room:id,name', 'client:id,nom,prenom'])
                ->latest('reservation_date')->limit(8)->get(),
        ]);
    }
}
