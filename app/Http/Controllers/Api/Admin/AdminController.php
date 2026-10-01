<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationRating;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{


    public function users(Request $request, string $role)
    {
        abort_unless(in_array($role, UserRole::values(), true), 404);

        $query = User::where('role', $role)->latest();
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('actif', $request->status === 'active');
        }

        return response()->json([
            'pagination' => $query->paginate(min((int) $request->get('per_page', 20), 100)),
            'stats' => [
                'total' => User::where('role', $role)->count(),
                'active' => User::where('role', $role)->where('actif', true)->count(),
                'inactive' => User::where('role', $role)->where('actif', false)->count(),
            ],
        ]);
    }

    public function updateUserStatus(Request $request, string $id)
    {
        $validated = $request->validate(['actif' => 'required|boolean']);
        $user = User::findOrFail($id);
        $user->update(['actif' => $validated['actif']]);

        return response()->json(['message' => 'Statut utilisateur mis à jour.', 'data' => $user]);
    }

    public function reservations(Request $request)
    {
        $query = Reservation::with([
            'room:id,name,description_fr,floor,capacity,price_per_day',
            'room.primaryImage:id,room_id,image_path',
            'client:id,nom,prenom,email,phone,profil',
            'receptionist:id,nom,prenom',
        ])->latest('reservation_date')->latest('start_time');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('client', function ($builder) use ($search) {
                $builder->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'pagination' => $query->paginate(min((int) $request->get('per_page', 20), 100)),
            'stats' => collect(ReservationStatus::cases())->mapWithKeys(fn ($status) => [
                $status->value => Reservation::where('status', $status)->count(),
            ]),
        ]);
    }

    public function updateReservationStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ReservationStatus::values())],
        ]);
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Statut de réservation mis à jour.', 'data' => $reservation]);
    }

}
