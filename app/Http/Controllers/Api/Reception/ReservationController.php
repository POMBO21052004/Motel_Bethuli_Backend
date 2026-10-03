<?php

namespace App\Http\Controllers\Api\Reception;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with([
            'room:id,name,description_fr',
            'room.primaryImage:id,room_id,image_path',
            'client:id,nom,prenom,email,phone,profil',
        ])->latest('reservation_date')->latest('start_time');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json(['pagination' => $query->paginate(min((int) $request->get('per_page', 20), 200))]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'client_id' => 'required|exists:users,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => ['nullable', Rule::in(ReservationStatus::values())],
        ]);

        $clientIsValid = DB::table('users')->where('id', $validated['client_id'])->where('role', 'client')->exists();
        if (!$clientIsValid) {
            return response()->json(['message' => 'Le compte sélectionné n’est pas un client.'], 422);
        }

        $room = Room::findOrFail($validated['room_id']);
        if ($room->status->value !== 'available') {
            return response()->json(['message' => 'Cette chambre n’est pas disponible.'], 422);
        }

        $hasConflict = Reservation::where('room_id', $room->id)
            ->whereDate('reservation_date', $validated['reservation_date'])
            ->whereNotIn('status', [ReservationStatus::CANCELLED->value])
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($hasConflict) {
            return response()->json(['message' => 'La chambre est déjà réservée sur ce créneau.'], 422);
        }

        $reservation = Reservation::create([
            ...$validated,
            'total_price' => $validated['total_price'] ?? $room->price_per_day ?? 0,
            'status' => $validated['status'] ?? ReservationStatus::CONFIRMED->value,
            'receptionist_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Réservation créée avec succès.',
            'data' => $reservation->load(['room:id,name', 'client:id,nom,prenom']),
        ], 201);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate(['status' => ['required', Rule::in(ReservationStatus::values())]]);
        $reservation = Reservation::findOrFail($id);
        $reservation->update([
            'status' => $validated['status'],
            'receptionist_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Réservation mise à jour.', 'data' => $reservation]);
    }
}
