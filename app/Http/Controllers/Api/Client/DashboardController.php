<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();
        return response()->json([
            'stats' => [
                'total' => $client->reservations()->count(),
                'pending' => $client->reservations()->where('status', ReservationStatus::PENDING)->count(),
                'confirmed' => $client->reservations()->where('status', ReservationStatus::CONFIRMED)->count(),
                'completed' => $client->reservations()->where('status', ReservationStatus::COMPLETED)->count(),
            ],
            'reservations' => $client->reservations()->with('room:id,name')->latest('reservation_date')->limit(6)->get(),
            'user' => $client->load('customerProfile'),
        ]);
    }
}
