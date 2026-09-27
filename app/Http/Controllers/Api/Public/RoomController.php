<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Enums\RoomStatus;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Liste des chambres pour le public (uniquement les disponibles).
     */
    public function index(Request $request)
    {
        $query = Room::with(['primaryImage', 'images'])
                     ->where('status', RoomStatus::AVAILABLE)
                     ->orderBy('floor')
                     ->orderBy('name');

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        // Pour la page d'accueil, on peut limiter le nombre
        if ($request->filled('limit')) {
            $rooms = $query->take($request->limit)->get();
            return response()->json(['data' => $rooms]);
        }

        $perPage = min((int) $request->get('per_page', 12), 50);
        $rooms = $query->paginate($perPage);

        return response()->json([
            'pagination' => $rooms,
        ]);
    }

    /**
     * Détail d'une chambre pour le public.
     */
    public function show(string $id)
    {
        $room = Room::with(['images'])->findOrFail($id);
        return response()->json(['data' => $room]);
    }
}
