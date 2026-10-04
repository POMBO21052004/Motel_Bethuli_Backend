<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Vérifie la disponibilité d'une chambre sur un créneau donné.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'reservation_date' => 'required|date',
            'end_date'         => 'required|date|after_or_equal:reservation_date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i',
            'exclude_id'       => 'nullable|string',
        ]);

        if ($validated['reservation_date'] === $validated['end_date'] && $validated['end_time'] <= $validated['start_time']) {
            return response()->json([
                'available'   => false,
                'message'     => "L'heure de fin doit être après l'heure de début pour une réservation le même jour.",
                'occupied_by' => null,
                'from'        => null,
                'until'       => null,
            ]);
        }

        $room = Room::findOrFail($validated['room_id']);
        if ($room->status->value !== 'available') {
            return response()->json([
                'available'   => false,
                'room_status' => $room->status->value,
                'message'     => "Cette chambre est en maintenance et ne peut pas recevoir de réservation.",
                'occupied_by' => null,
                'from'        => null,
                'until'       => null,
            ]);
        }

        $query = Reservation::where('room_id', $validated['room_id'])
            ->whereNotIn('status', [ReservationStatus::CANCELLED->value])
            ->where(function ($q) use ($validated) {
                $q->where('reservation_date', '<', $validated['end_date'])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('reservation_date', '=', $validated['end_date'])
                         ->where('start_time', '<', $validated['end_time']);
                  });
            })
            ->where(function ($q) use ($validated) {
                $q->where('end_date', '>', $validated['reservation_date'])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('end_date', '=', $validated['reservation_date'])
                         ->where('end_time', '>', $validated['start_time']);
                  });
            });

        if (!empty($validated['exclude_id'])) {
            $query->where('id', '!=', $validated['exclude_id']);
        }

        $conflict = $query->with(['client:id,nom,prenom'])->first();

        if ($conflict) {
            $fromDate = $conflict->reservation_date instanceof \Carbon\Carbon ? $conflict->reservation_date->format('d/m/Y') : $conflict->reservation_date;
            $untilDate = $conflict->end_date instanceof \Carbon\Carbon ? $conflict->end_date->format('d/m/Y') : $conflict->end_date;

            return response()->json([
                'available' => false,
                'message'   => 'Cette chambre est déjà réservée sur ce créneau.',
                'occupied_by' => $conflict->client ? trim($conflict->client->prenom . ' ' . $conflict->client->nom) : 'un client',
                'from' => $fromDate . ' à ' . substr($conflict->start_time, 0, 5),
                'until' => $untilDate . ' à ' . substr($conflict->end_time, 0, 5),
            ]);
        }

        return response()->json([
            'available' => true,
            'message'   => 'La chambre est disponible pour ce créneau.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'client_id'        => 'required|exists:users,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'end_date'         => 'required|date|after_or_equal:reservation_date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i',
            'total_price'      => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:2000',
            'status'           => ['nullable', Rule::in(ReservationStatus::values())],
        ]);

        if ($validated['reservation_date'] === $validated['end_date'] && $validated['end_time'] <= $validated['start_time']) {
            return response()->json(['message' => 'L\'heure de fin doit être après l\'heure de début pour une réservation le même jour.'], 422);
        }

        $clientExists = DB::table('users')
            ->where('id', $validated['client_id'])
            ->where('role', 'client')
            ->exists();
        if (!$clientExists) {
            return response()->json(['message' => 'Le compte sélectionné n\'est pas un client.'], 422);
        }

        $room = Room::findOrFail($validated['room_id']);
        if ($room->status->value !== 'available') {
            return response()->json(['message' => 'Cette chambre est en maintenance et ne peut pas recevoir de réservation.'], 422);
        }

        $hasConflict = Reservation::where('room_id', $room->id)
            ->whereNotIn('status', [ReservationStatus::CANCELLED->value])
            ->where(function ($q) use ($validated) {
                $q->where('reservation_date', '<', $validated['end_date'])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('reservation_date', '=', $validated['end_date'])
                         ->where('start_time', '<', $validated['end_time']);
                  });
            })
            ->where(function ($q) use ($validated) {
                $q->where('end_date', '>', $validated['reservation_date'])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('end_date', '=', $validated['reservation_date'])
                         ->where('end_time', '>', $validated['start_time']);
                  });
            })
            ->exists();

        if ($hasConflict) {
            return response()->json(['message' => 'La chambre est déjà réservée sur ce créneau.'], 422);
        }

        $reservation = Reservation::create([
            ...$validated,
            'total_price'     => $validated['total_price'] ?? $room->price_per_day ?? 0,
            'status'          => $validated['status'] ?? ReservationStatus::CONFIRMED->value,
            'receptionist_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Réservation créée avec succès.',
            'data'    => $reservation->load(['room:id,name', 'client:id,nom,prenom']),
        ], 201);
    }

    public function show($id)
    {
        $reservation = Reservation::with(['room.images', 'client'])->findOrFail($id);
        return response()->json(['data' => $reservation]);
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $validated = $request->validate([
            'room_id'          => 'sometimes|exists:rooms,id',
            'client_id'        => 'sometimes|exists:users,id',
            'reservation_date' => 'sometimes|date|after_or_equal:today',
            'end_date'         => 'sometimes|date|after:reservation_date',
            'start_time'       => 'sometimes|date_format:H:i',
            'end_time'         => 'sometimes|date_format:H:i',
            'total_price'      => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:2000',
            'status'           => ['nullable', Rule::in(ReservationStatus::values())],
        ]);

        if (isset($validated['room_id']) || isset($validated['reservation_date']) || isset($validated['end_date']) || isset($validated['start_time']) || isset($validated['end_time'])) {
            $roomId    = $validated['room_id'] ?? $reservation->room_id;
            $resDate   = $validated['reservation_date'] ?? $reservation->reservation_date;
            $endDate   = $validated['end_date'] ?? $reservation->end_date;
            $startTime = $validated['start_time'] ?? $reservation->start_time;
            $endTime   = $validated['end_time'] ?? $reservation->end_time;
            $status    = $validated['status'] ?? $reservation->status;

            if ($resDate === $endDate && $endTime <= $startTime) {
                return response()->json(['message' => 'L\'heure de fin doit être après l\'heure de début pour une réservation le même jour.'], 422);
            }

            $hasConflict = Reservation::where('id', '!=', $id)
                ->where('room_id', $roomId)
                ->whereNotIn('status', [ReservationStatus::CANCELLED->value])
                ->where(function ($q) use ($resDate, $endDate, $startTime, $endTime) {
                    $q->where('reservation_date', '<', $endDate)
                      ->orWhere(function ($q2) use ($endDate, $endTime) {
                          $q2->where('reservation_date', '=', $endDate)
                             ->where('start_time', '<', $endTime);
                      });
                })
                ->where(function ($q) use ($resDate, $endDate, $startTime, $endTime) {
                    $q->where('end_date', '>', $resDate)
                      ->orWhere(function ($q2) use ($resDate, $startTime) {
                          $q2->where('end_date', '=', $resDate)
                             ->where('end_time', '>', $startTime);
                      });
                })
                ->exists();

            if ($hasConflict && $status !== ReservationStatus::CANCELLED->value) {
                return response()->json(['message' => 'La chambre est déjà réservée sur ce créneau.'], 422);
            }
        }

        $reservation->update($validated);

        return response()->json([
            'message' => 'Réservation mise à jour avec succès.',
            'data'    => $reservation->fresh()->load(['room', 'client']),
        ]);
    }

    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        return response()->json(['message' => 'Réservation supprimée avec succès.']);
    }
}
