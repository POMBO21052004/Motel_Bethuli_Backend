<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationRating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $query = ReservationRating::with([
            'client:id,nom,prenom,email',
            'reservation:id,room_id,reservation_date',
            'reservation.room:id,name',
        ])->latest();

        if ($request->filled('rating') && $request->rating !== 'all') {
            $query->where('rating', $request->rating);
        }

        return response()->json([
            'pagination' => $query->paginate(min((int) $request->get('per_page', 20), 100)),
            'stats' => [
                'total' => ReservationRating::count(),
                'average' => round((float) ReservationRating::avg('rating'), 1),
            ],
        ]);
    }

    public function destroy(string $id)
    {
        ReservationRating::findOrFail($id)->delete();
        return response()->json(['message' => 'Avis supprimé avec succès.']);
    }
}
