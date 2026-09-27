<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ReservationRating;
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
}