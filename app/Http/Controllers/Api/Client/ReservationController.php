<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Enums\ReservationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->reservations()->with([
            'room:id,name,description_fr,price_per_day,floor,capacity',
            'room.primaryImage:id,room_id,image_path',
            'rating',
        ])->latest('reservation_date');
        
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json(['pagination' => $query->paginate(20)]);
    }

    public function show(Request $request, string $id)
    {
        $reservation = $request->user()->reservations()
            ->with([
                'room:id,name,description_fr,price_per_day,floor,capacity,status',
                'room.primaryImage:id,room_id,image_path',
                'room.images:id,room_id,image_path',
                'rating',
            ])
            ->findOrFail($id);

        return response()->json(['data' => $reservation]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        // Vérification de la CNI
        $profile = $user->customerProfile;
        if (!$profile || !$profile->cni_verified) {
            return response()->json([
                'message' => 'Votre compte n\'est pas encore vérifié. Veuillez mettre à jour votre CNI dans votre profil pour pouvoir réserver.'
            ], 403);
        }

        $validated = $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'end_date'         => 'required|date|after:reservation_date',  // strictly after: minimum 1 nuit
            'notes'            => 'nullable|string|max:2000',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        if ($room->status->value !== 'available') {
            return response()->json(['message' => 'Cette chambre n\'est pas disponible pour le moment.'], 422);
        }

        // Vérification des conflits (chevauchement) — exclut CANCELLED et COMPLETED
        $excludedStatuses = [
            ReservationStatus::CANCELLED->value,
            ReservationStatus::COMPLETED->value,
        ];

        $conflict = Reservation::where('room_id', $room->id)
            ->whereNotIn('status', $excludedStatuses)
            ->where(function ($q) use ($validated) {
                $q->whereDate('reservation_date', '<', $validated['end_date'])
                  ->whereDate('end_date', '>', $validated['reservation_date']);
            })
            ->first();

        if ($conflict) {
            $from  = \Carbon\Carbon::parse($conflict->reservation_date)->format('d/m/Y');
            $until = \Carbon\Carbon::parse($conflict->end_date)->format('d/m/Y');
            return response()->json([
                'message' => "Cette chambre est déjà réservée du {$from} au {$until}. Veuillez choisir d'autres dates.",
            ], 422);
        }

        $days = Carbon::parse($validated['reservation_date'])->diffInDays(Carbon::parse($validated['end_date']));
        $days = $days == 0 ? 1 : $days;
        
        $reservation = Reservation::create([
            'room_id'          => $room->id,
            'client_id'        => $user->id,
            'reservation_date' => $validated['reservation_date'],
            'end_date'         => $validated['end_date'],
            'start_time'       => '12:00:00',
            'end_time'         => '12:00:00',
            'total_price'      => ($room->price_per_day ?? 0) * $days,
            'notes'            => $validated['notes'] ?? null,
            'status'           => ReservationStatus::PENDING->value,
        ]);

        return response()->json([
            'message' => 'Réservation créée avec succès. Veuillez contacter le support WhatsApp pour la confirmer.',
            'data'    => $reservation->load('room:id,name,floor'),
        ], 201);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'reservation_date' => 'required|date',
            'end_date'         => 'required|date|after:reservation_date',  // minimum 1 nuit
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if ($room->status->value !== 'available') {
            return response()->json([
                'available' => false,
                'message'   => 'Cette chambre est actuellement en maintenance ou indisponible.',
            ]);
        }

        $excludedStatuses = [
            ReservationStatus::CANCELLED->value,
            ReservationStatus::COMPLETED->value,
        ];

        $conflict = Reservation::where('room_id', $room->id)
            ->whereNotIn('status', $excludedStatuses)
            ->where(function ($q) use ($validated) {
                $q->whereDate('reservation_date', '<', $validated['end_date'])
                  ->whereDate('end_date', '>', $validated['reservation_date']);
            })
            ->first();

        if ($conflict) {
            $from  = \Carbon\Carbon::parse($conflict->reservation_date)->format('d/m/Y');
            $until = \Carbon\Carbon::parse($conflict->end_date)->format('d/m/Y');
            return response()->json([
                'available' => false,
                'message'   => "La chambre est déjà réservée du {$from} au {$until}.",
                'from'      => $conflict->reservation_date,
                'until'     => $conflict->end_date,
            ]);
        }

        $days = Carbon::parse($validated['reservation_date'])->diffInDays(Carbon::parse($validated['end_date']));
        $days = $days == 0 ? 1 : $days;

        return response()->json([
            'available' => true,
            'message'   => 'La chambre est disponible pour ces dates.',
            'price'     => ($room->price_per_day ?? 0) * $days,
            'days'      => $days,
        ]);
    }
}
